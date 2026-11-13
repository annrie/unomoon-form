#!/usr/bin/env bash
# 上流 MW WP Form に、このフォークがまだ追いついていない版が出ていないかを確かめる。
# .github/workflows/upstream-check.yml から呼ばれる。手元でも実行できる(gh と jq と curl が要る)。
#
# 比べるもの:
#   - このフォークの追随先 = readme.txt の Stable tag の先頭 3 桁(5.1.8.2 → 5.1.8)
#   - 上流の GitHub のタグ(web-soudan/mw-wp-form。上流は GitHub Release を作らない)
#   - 上流の WordPress.org の版(配布漏れでタグと .org がずれたことがある。5.1.7 → 5.1.8)
# どちらか新しいほうが追随先より新しければ、その版の Issue を立てる。
# 版は 3 桁にそろえて比べる(上流の 5.2 は 5.2.0 として扱い、フォークは 5.2.0.1 になる)。
#
# 同じ版の Issue は二度立てない。ラベル upstream-check の付いた Issue を、開閉を問わず
# 本文の目印(<!-- upstream-check: 版 -->)で探すので、題名を変えても閉じても重複しない。
#
# 環境変数:
#   GH_TOKEN      gh が使うトークン(Actions では GITHUB_TOKEN)
#   REPO          Issue を立てるリポジトリ(既定 annrie/unomoon-form)
#   STABLE_TAG    Stable tag を readme.txt から読まずに指定する(試験用)
#   DRY_RUN=1     Issue もラベルも作らずに、立てる内容を表示するだけにする
set -euo pipefail

UPSTREAM_REPO="web-soudan/mw-wp-form"
UPSTREAM_SLUG="mw-wp-form"
REPO="${REPO:-annrie/unomoon-form}"
DRY_RUN="${DRY_RUN:-0}"
LABEL="upstream-check"
VERSION_RE='^[0-9]+(\.[0-9]+)*$'
THREE_RE='^[0-9]+\.[0-9]+\.[0-9]+$'

# a が b より新しい版なら真
version_gt() {
	[ "$1" != "$2" ] && [ "$(printf '%s\n%s\n' "$1" "$2" | sort -V | tail -1)" = "$1" ]
}

# 3 桁に満たない版を .0 で埋める(5.2 → 5.2.0)。4 桁以上はそのまま返す
pad3() {
	local v="$1"
	while [ "$(printf '%s' "${v}" | tr -cd '.' | wc -c | tr -d ' ')" -lt 2 ]; do
		v="${v}.0"
	done
	printf '%s\n' "${v}"
}

STABLE_TAG="${STABLE_TAG:-$(sed -n 's/^Stable tag: *//p' readme.txt | head -1 | tr -d '\r')}"
if ! [[ "${STABLE_TAG}" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
	echo "::error::Stable tag が「上流の版(3 桁).連番」の形ではありません: '${STABLE_TAG}'"
	exit 1
fi
BASE="${STABLE_TAG%.*}"

# API はタグを版の順に返すとは限らない(5.1.10 と 5.1.9)。全ページ取って並べ直す
if ! TAGS=$(gh api --paginate "repos/${UPSTREAM_REPO}/tags?per_page=100" --jq '.[].name'); then
	echo "::error::上流の GitHub のタグを取得できませんでした(gh api repos/${UPSTREAM_REPO}/tags が失敗。理由は直前のログ)"
	exit 1
fi
TAG_LATEST=$(printf '%s\n' "${TAGS}" | grep -E "${VERSION_RE}" | sort -V | tail -1 || true)

# -g が無いと curl が URL の [ ] を範囲指定として展開して失敗する
ORG_URL="https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slug]=${UPSTREAM_SLUG}&request[fields][sections]=0"
if ! ORG_JSON=$(curl -fsSg --retry 3 --max-time 30 "${ORG_URL}"); then
	echo "::error::上流の WordPress.org の情報を取得できませんでした(curl が失敗。理由は直前のログ)"
	exit 1
fi
ORG_LATEST=$(printf '%s' "${ORG_JSON}" | jq -r '.version // empty' 2>/dev/null || true)

# 外から来た値なので、題名や本文に入れる前に版の形かどうかを確かめる
if ! [[ "${TAG_LATEST}" =~ ${VERSION_RE} ]]; then
	echo "::error::上流の GitHub のタグに版の形のものがありません(最新として読めた値: '${TAG_LATEST}')"
	exit 1
fi
if ! [[ "${ORG_LATEST}" =~ ${VERSION_RE} ]]; then
	echo "::error::上流の WordPress.org の応答から版を読めませんでした(読めた値: '${ORG_LATEST}')"
	exit 1
fi

LATEST="${TAG_LATEST}"
if version_gt "$(pad3 "${ORG_LATEST}")" "$(pad3 "${LATEST}")"; then
	LATEST="${ORG_LATEST}"
fi
LATEST3=$(pad3 "${LATEST}")

echo "このフォーク: Stable tag ${STABLE_TAG}(上流 ${BASE} に追随)"
echo "上流: GitHub のタグ ${TAG_LATEST} / WordPress.org ${ORG_LATEST}"

if ! version_gt "${LATEST3}" "${BASE}"; then
	echo "上流に新しい版はありません。"
	exit 0
fi

TITLE="上流 MW WP Form ${LATEST} が出ています"
MARKER="<!-- upstream-check: ${LATEST3} -->"

if [ "${DRY_RUN}" != "1" ]; then
	# 既にあれば作り直さない(--force は色と説明を上書きするだけ)
	gh label create "${LABEL}" --repo "${REPO}" --force \
		--color "c5def5" --description "上流 MW WP Form の新しい版(upstream-check.yml が立てる)" >/dev/null
fi

EXISTING=$(gh issue list --repo "${REPO}" --state all --label "${LABEL}" --limit 200 --json number,body |
	jq -r --arg m "${MARKER}" '[.[] | select(.body | contains($m))][0].number // empty')
if [ -n "${EXISTING}" ]; then
	echo "版 ${LATEST3} の Issue #${EXISTING} が既にあるので、新しくは立てません。"
	exit 0
fi

NOTES=""
if [ "${TAG_LATEST}" != "${ORG_LATEST}" ]; then
	NOTES="${NOTES}
> [!NOTE]
> GitHub のタグ(${TAG_LATEST})と WordPress.org の版(${ORG_LATEST})が一致していません。配布漏れや出し直しのことがあるので、両方を見てください。
"
fi

if [ "${LATEST}" = "${TAG_LATEST}" ]; then
	DIFF_LINE="- 差分: https://github.com/${UPSTREAM_REPO}/compare/${BASE}...${TAG_LATEST}"
else
	DIFF_LINE="- 差分: 上流の GitHub には ${LATEST} のタグがまだありません。いまの master と比べる: https://github.com/${UPSTREAM_REPO}/compare/${BASE}...master
- WordPress.org の SVN: https://plugins.trac.wordpress.org/browser/${UPSTREAM_SLUG}/tags/${LATEST}"
fi

if [[ "${LATEST3}" =~ ${THREE_RE} ]]; then
	NEXT="${LATEST3}.1"
	if [ "${LATEST}" != "${LATEST3}" ]; then
		NOTES="${NOTES}
> [!NOTE]
> 上流の版 ${LATEST} は 3 桁ではないので、${LATEST3} として扱いました。フォークの版は ${NEXT} です。
"
	fi
else
	NEXT="(要検討)"
	NOTES="${NOTES}
> [!WARNING]
> 上流の版 ${LATEST} が 4 桁以上です。「上流の版.連番」の付け方では、フォークの版(5.1.8.1 など)と見分けられません。番号の付け方を決めてから移植してください。
"
fi

BODY=$(cat <<EOF
上流に、このフォークがまだ追いついていない版があります。

| | 版 |
| --- | --- |
| このフォークの Stable tag | ${STABLE_TAG}(上流 ${BASE} に追随) |
| 上流の GitHub のタグ | ${TAG_LATEST} |
| 上流の WordPress.org | ${ORG_LATEST} |
${NOTES}
${DIFF_LINE}
- 上流の変更履歴: https://wordpress.org/plugins/${UPSTREAM_SLUG}/#developers

## 移植の手順

- [ ] 差分を読み、セキュリティ修正かどうかを確かめる(コードの変更が無い出し直しのこともある)
- [ ] 上流の \`tests/\` の差分を、Local で \`wp eval-file\` に写して確かめる(このフォークにはテストが無い)
- [ ] develop から作業ブランチを切って移植し、\`Version:\` と \`Stable tag:\` を ${NEXT} に上げて Changelog を書く(日英)
- [ ] PR の前に \`/code-review\` を回す → develop へ PR → Codex レビュー → マージ → develop を main へマージ
- [ ] \`v${NEXT}\` のタグを push する(WordPress.org へ配信される)
- [ ] 本番 2 サイトを \`wp plugin update unomoon-form --version=${NEXT}\` で更新する
- [ ] 利用者に影響があれば cielos の解説ページを更新する

移植しないと決めたときは、理由を書いて閉じてください。閉じた版の Issue は、もう立ちません。

この Issue は .github/workflows/upstream-check.yml が立てました。

${MARKER}
EOF
)

if [ "${DRY_RUN}" = "1" ]; then
	echo "[dry-run] 次の Issue を立てます: ${TITLE}"
	echo "${BODY}"
	exit 0
fi

URL=$(gh issue create --repo "${REPO}" --title "${TITLE}" --label "${LABEL}" --body "${BODY}")
echo "Issue を立てました: ${URL}"
echo "::notice::${TITLE} ${URL}"

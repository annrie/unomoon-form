#!/bin/bash
# unomoon-form 配布用 zip 生成スクリプト
# WordPress.org へ提出する zip には配布物だけを含める(allowlist 方式)。
# 除外: assets/(.org 用スクリーンショット) languages/(translate.wordpress.org で管理)
#       README.md scripts/ tools/ tasks/ packaged/ .git .DS_Store
# WordPress.org への配信は .github/workflows/deploy-wordpress-org.yml が .distignore を使って行う。
# ここの allowlist を変えたら .distignore も同じ結果になるように更新すること。

set -e

PLUGIN_DIR="$(cd "$(dirname "$0")/.." && pwd)"
PLUGIN_NAME="unomoon-form"
VERSION=$(sed -n 's/^ \* Version: *//p' "${PLUGIN_DIR}/${PLUGIN_NAME}.php" | head -1)
ARCHIVE_DIR="${PLUGIN_DIR}/packaged"
ARCHIVE_NAME="${PLUGIN_NAME}-${VERSION}.zip"
TEMP_DIR="${ARCHIVE_DIR}/.temp-${PLUGIN_NAME}"

log_info() { echo -e "\033[1;34m[INFO]\033[0m $1"; }
log_success() { echo -e "\033[1;32m[SUCCESS]\033[0m $1"; }

if [ -z "${VERSION}" ]; then
  echo "Version header not found in ${PLUGIN_NAME}.php" >&2
  exit 1
fi

# readme.txt の Stable tag とヘッダの Version が一致していることを確認
STABLE_TAG=$(sed -n 's/^Stable tag: *//p' "${PLUGIN_DIR}/readme.txt" | head -1)
if [ "${STABLE_TAG}" != "${VERSION}" ]; then
  echo "Stable tag (${STABLE_TAG}) と Version (${VERSION}) が一致していません" >&2
  exit 1
fi

log_info "packaged ディレクトリを準備中..."
mkdir -p "${ARCHIVE_DIR}"
rm -rf "${TEMP_DIR}"
mkdir -p "${TEMP_DIR}/${PLUGIN_NAME}"

log_info "プラグインファイルをコピー中..."
for item in "${PLUGIN_NAME}.php" readme.txt LICENSE classes css images js templates; do
  cp -R "${PLUGIN_DIR}/${item}" "${TEMP_DIR}/${PLUGIN_NAME}/"
  log_info "  - ${item}"
done

find "${TEMP_DIR}" -name ".DS_Store" -delete 2>/dev/null || true

log_info "ZIP アーカイブを作成中..."
rm -f "${ARCHIVE_DIR}/${ARCHIVE_NAME}"
cd "${TEMP_DIR}"
zip -rq "${ARCHIVE_DIR}/${ARCHIVE_NAME}" "${PLUGIN_NAME}"
rm -rf "${TEMP_DIR}"

log_success "プラグインパッケージ作成完了！"
echo ""
echo "  📦 ${ARCHIVE_DIR}/${ARCHIVE_NAME}"
echo "  サイズ: $(du -h "${ARCHIVE_DIR}/${ARCHIVE_NAME}" | cut -f1)"
echo ""
unzip -l "${ARCHIVE_DIR}/${ARCHIVE_NAME}"

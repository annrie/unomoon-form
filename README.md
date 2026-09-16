# Unomoon Form

<p align="center">
  <!-- License -->
  <a href="LICENSE">
    <img src="https://img.shields.io/github/license/annrie/unomoon-form.svg" alt="License">
  </a>
  <!-- Stars -->
  <a href="https://github.com/annrie/unomoon-form/stargazers">
    <img src="https://img.shields.io/github/stars/annrie/unomoon-form.svg" alt="Stars">
  </a>
  <!-- Last commit -->
  <a href="https://github.com/annrie/unomoon-form/commits">
    <img src="https://img.shields.io/github/last-commit/annrie/unomoon-form.svg" alt="Last commit">
  </a>
</p>

Unomoon Form is a WordPress 7 compatible fork of MW WP Form.

Unomoon Form は、MW WP Form の開発停止を受けてフォークした、WordPress 7.1 検証済みのフォームプラグインです。

MW WP Form development has stopped, so this fork migrates the plugin to the `unomoon-form` namespace and keeps the shortcode-based form workflow available for current WordPress environments.

MW WP Form の開発が停止したため、このフォークではプラグインを `unomoon-form` 名前空間へ移行し、ショートコードベースのフォーム作成ワークフローを現在の WordPress 環境で利用できるようにしています。

## Features / 主な機能

- Shortcode-based form creation / ショートコードによるフォーム作成
- Confirmation screen support / 確認画面
- Same-page or separate-page transitions / 同一URLまたは個別URLでの画面遷移
- Validation rules / バリデーションルール
- Admin notification email and automatic reply email / 管理者宛メールと自動返信メール
- Inquiry data storage / 問い合わせデータ保存
- Chart display for saved inquiry data / 保存データのグラフ表示
- Japanese translation via translate.wordpress.org / 日本語翻訳（translate.wordpress.org 経由）
- No external scripts or styles are loaded (Chart.js and jQuery UI theme are bundled) / 外部スクリプト・スタイルの読み込みなし（Chart.js と jQuery UI テーマを同梱）

## Namespace Changes / 名前空間の変更

This fork uses Unomoon Form identifiers.

このフォークでは Unomoon Form の識別子を使用します。

- Plugin slug: `unomoon-form`
- Post type: `unomoon-form`
- Shortcode prefix: `unomoonform_*`
- Hook prefix: `unomoonform_*`
- Frontend wrapper class: `.unomoon_form`

Existing MW WP Form data should be migrated intentionally before production use.

既存の MW WP Form データを利用する場合は、本番利用前に意図的に移行してください。

### Migrating from Uno WP Form (≤ 5.1.6.1) / Uno WP Form からの移行

This plugin was named **Uno WP Form** until 5.1.6.1 and was renamed to comply with the WordPress.org naming rules. Every identifier changed (`uno-wp-form` → `unomoon-form`, `unoform_*` → `unomoonform_*`, inquiry post types `uwf_*` → `unomoon_*`, meta keys `uwf_*` → `unomoonform_*`). To upgrade an existing site:

1. Back up the database.
2. Deactivate Uno WP Form (and Uno WP Form reCAPTCHA if installed) and install Unomoon Form.
3. Run the migration script with WP-CLI: `wp eval-file tools/migrate-from-uno-wp-form.php dry-run` (positional argument), check the counts, then run it again without `dry-run`.
4. Activate Unomoon Form.

このプラグインは 5.1.6.1 まで **Uno WP Form** という名前でしたが、WordPress.org の命名規則に合わせて改名しました。すべての識別子が変わっています（`uno-wp-form` → `unomoon-form`、`unoform_*` → `unomoonform_*`、問い合わせデータの post type `uwf_*` → `unomoon_*`、meta キー `uwf_*` → `unomoonform_*`）。既存サイトは、DB をバックアップした上で旧プラグインを無効化し、`wp eval-file tools/migrate-from-uno-wp-form.php dry-run`（位置引数）で件数を確認してから `dry-run` なしで実行し、その後 Unomoon Form を有効化してください。

## Requirements / 動作要件

- WordPress 6.0 or later / WordPress 6.0 以上
- Tested up to WordPress 7.1 / WordPress 7.1 検証済み
- PHP 8.0 or later / PHP 8.0 以上

## Installation / インストール

1. Upload the `unomoon-form` directory to `wp-content/plugins/`.
2. Activate `Unomoon Form` in the WordPress admin.
3. Create or edit forms from the `Unomoon Form` admin menu.
4. Place the generated shortcode on a page.

1. `unomoon-form` ディレクトリを `wp-content/plugins/` にアップロードします。
2. WordPress管理画面で `Unomoon Form` を有効化します。
3. `Unomoon Form` の管理メニューからフォームを作成または編集します。
4. 生成されたショートコードを固定ページなどに配置します。

## Releasing to WordPress.org / WordPress.org へのリリース

Development happens on GitHub; the WordPress.org SVN repository is only a release target. Pushing a `v<version>` tag runs `.github/workflows/deploy-wordpress-org.yml`, which deploys `trunk/`, `tags/<version>/` and `assets/` via [10up/action-wordpress-plugin-deploy](https://github.com/10up/action-wordpress-plugin-deploy) and attaches the generated zip to a GitHub Release. Files listed in `.distignore` are excluded from `trunk/`; the workflow fails if `.distignore` and the `scripts/package.sh` allowlist produce different file sets.

1. Bump `Version:` in `unomoon-form.php` and `Stable tag:` in `readme.txt` to the same value, update the changelog, and merge to `main`.
2. `git tag v5.1.6.2 && git push origin v5.1.6.2` (the workflow fails if the tag, the `Version:` header and `Stable tag:` do not match).
3. Optional: run the workflow manually from the Actions tab to perform a dry run (nothing is committed to SVN; the resulting zip is uploaded as a workflow artifact). The secrets below are required even for a dry run.

Required repository secrets: `SVN_USERNAME` (WordPress.org username) and `SVN_PASSWORD` (generated under Account & Security on your WordPress.org profile).

開発は GitHub で行い、WordPress.org の SVN はリリース先としてのみ使います。`v<version>` タグを push すると `.github/workflows/deploy-wordpress-org.yml` が動き、[10up/action-wordpress-plugin-deploy](https://github.com/10up/action-wordpress-plugin-deploy) で `trunk/`・`tags/<version>/`・`assets/` を配信し、生成された zip を GitHub Release に添付します。`.distignore` に列挙したファイルは `trunk/` から除外され、`.distignore` と `scripts/package.sh` の allowlist の結果が食い違うとワークフローは失敗します。

1. `unomoon-form.php` の `Version:` と `readme.txt` の `Stable tag:` を同じ値に上げ、changelog を更新して `main` にマージします。
2. `git tag v5.1.6.2 && git push origin v5.1.6.2` を実行します（タグ・`Version:` ヘッダ・`Stable tag:` が一致しないとワークフローは失敗します）。
3. 任意: Actions タブからワークフローを手動実行すると dry run になります（SVN へはコミットせず、zip をワークフローの artifact として保存します）。dry run でも下記の Secrets は必要です。

必要なリポジトリ Secrets: `SVN_USERNAME`（WordPress.org のユーザー名）と `SVN_PASSWORD`（WordPress.org プロフィールの Account & Security で生成）。

## Upstream / フォーク元

This project is forked from MW WP Form:

このプロジェクトは MW WP Form からフォークしています。

https://github.com/web-soudan/mw-wp-form

The fork exists because upstream development has stopped.

フォークした理由は、フォーク元の開発が停止したためです。

## Related Plugin / 関連プラグイン

Unomoon Form reCAPTCHA:

https://github.com/annrie/unomoon-form-recaptcha

## License / ライセンス

GPLv2 or later.

GPLv2 またはそれ以降。

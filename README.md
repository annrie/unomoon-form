# Unomoon Form

<p align="center">
  <!-- License -->
  <a href="LICENSE">
    <img src="https://img.shields.io/github/license/annrie/unomoon-form.svg" alt="License">
  </a>
  <!-- WordPress.org plugin version -->
  <a href="https://wordpress.org/plugins/unomoon-form/">
    <img src="https://img.shields.io/wordpress/plugin/v/unomoon-form.svg" alt="WordPress.org plugin version">
  </a>
  <!-- WordPress.org downloads -->
  <a href="https://wordpress.org/plugins/unomoon-form/advanced/">
    <img src="https://img.shields.io/wordpress/plugin/dt/unomoon-form.svg" alt="WordPress.org downloads">
  </a>
  <!-- WordPress.org active installs -->
  <a href="https://wordpress.org/plugins/unomoon-form/advanced/">
    <img src="https://img.shields.io/wordpress/plugin/installs/unomoon-form.svg" alt="WordPress.org active installs">
  </a>
  <!-- Latest release -->
  <a href="https://github.com/annrie/unomoon-form/releases/latest">
    <img src="https://img.shields.io/github/v/release/annrie/unomoon-form.svg" alt="Latest release">
  </a>
  <!-- Downloads total -->
  <a href="https://github.com/annrie/unomoon-form/releases">
    <img src="https://img.shields.io/github/downloads/annrie/unomoon-form/total.svg" alt="Total downloads">
  </a>
  <!-- Downloads latest release -->
  <a href="https://github.com/annrie/unomoon-form/releases/latest">
    <img src="https://img.shields.io/github/downloads/annrie/unomoon-form/latest/total.svg" alt="Latest release downloads">
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

Unomoon Form is a fork of MW WP Form, tested up to WordPress 7.1.

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

The plugin is available in the [WordPress.org plugin directory](https://wordpress.org/plugins/unomoon-form/).

1. In the WordPress admin, go to **Plugins → Add New Plugin** and search for "Unomoon Form".
2. Click **Install Now**, then **Activate**.
3. Create or edit forms from the `Unomoon Form` admin menu.
4. Place the generated shortcode on a page.

To install manually instead, download `unomoon-form-<version>.zip` from the [Releases page](https://github.com/annrie/unomoon-form/releases/latest) and upload it via **Plugins → Add New Plugin → Upload Plugin**, or unzip it into `wp-content/plugins/`. Do not use the "Download ZIP" button on GitHub: that archive contains development files and its top-level directory name will not match the plugin slug.

このプラグインは [WordPress.org のプラグインディレクトリ](https://wordpress.org/plugins/unomoon-form/) で公開されています。

1. WordPress 管理画面の **プラグイン → 新規プラグインを追加** で「Unomoon Form」を検索します。
2. **今すぐインストール** → **有効化** をクリックします。
3. `Unomoon Form` の管理メニューからフォームを作成または編集します。
4. 生成されたショートコードを固定ページなどに配置します。

手動でインストールする場合は、[Releases ページ](https://github.com/annrie/unomoon-form/releases/latest) から `unomoon-form-<version>.zip` をダウンロードし、**プラグイン → 新規プラグインを追加 → プラグインのアップロード** から追加するか、`wp-content/plugins/` に展開してください。GitHub の「Download ZIP」は開発用ファイルを含み、ディレクトリ名もプラグインのスラッグと一致しないので使わないでください。

## Releasing / リリース

Development happens on GitHub; the WordPress.org SVN repository is only a release target. Merging a pull request does not publish anything — pushing a `v<version>` tag runs the deploy workflow, which pushes the plugin to WordPress.org and attaches the zip to a GitHub Release. Pull requests targeting `main` are also checked with [Plugin Check](https://wordpress.org/plugins/plugin-check/). Maintainer notes live at the top of `.github/workflows/deploy-wordpress-org.yml`.

開発は GitHub で行い、WordPress.org の SVN はリリース先としてのみ使います。プルリクエストをマージしただけでは公開されず、`v<version>` タグを push するとデプロイワークフローが WordPress.org へ配信し、zip を GitHub Release に添付します。また、`main` 宛のプルリクエストでは [Plugin Check](https://wordpress.org/plugins/plugin-check/) が実行されます。保守者向けの手順は `.github/workflows/deploy-wordpress-org.yml` の冒頭コメントにあります。

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

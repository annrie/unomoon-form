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

[English](#english) | [日本語](#日本語)

## English

Unomoon Form is a fork of MW WP Form, tested up to WordPress 7.1.

MW WP Form development has stopped, so this fork migrates the plugin to the `unomoon-form` namespace and keeps the shortcode-based form workflow available for current WordPress environments.

### Features

- Shortcode-based form creation
- Confirmation screen support
- Same-page or separate-page transitions
- Validation rules
- Admin notification email and automatic reply email
- Inquiry data storage
- Chart display for saved inquiry data
- Japanese translation via translate.wordpress.org
- No external scripts or styles are loaded (Chart.js and jQuery UI theme are bundled)

### Namespace changes

This fork uses Unomoon Form identifiers.

- Plugin slug: `unomoon-form`
- Post type: `unomoon-form`
- Shortcode prefix: `unomoonform_*`
- Hook prefix: `unomoonform_*`
- Frontend wrapper class: `.unomoon_form`

Existing MW WP Form data should be migrated intentionally before production use.

#### Migrating from Uno WP Form (≤ 5.1.6.1)

This plugin was named **Uno WP Form** until 5.1.6.1 and was renamed to comply with the WordPress.org naming rules. Every identifier changed (`uno-wp-form` → `unomoon-form`, `unoform_*` → `unomoonform_*`, inquiry post types `uwf_*` → `unomoon_*`, meta keys `uwf_*` → `unomoonform_*`). To upgrade an existing site:

1. Back up the database.
2. Deactivate Uno WP Form (and Uno WP Form reCAPTCHA if installed) and install Unomoon Form.
3. Run the migration script with WP-CLI: `wp eval-file tools/migrate-from-uno-wp-form.php dry-run` (positional argument), check the counts, then run it again without `dry-run`.
4. Activate Unomoon Form.

### Requirements

- WordPress 6.0 or later
- Tested up to WordPress 7.1
- PHP 8.0 or later

### Installation

The plugin is available in the [WordPress.org plugin directory](https://wordpress.org/plugins/unomoon-form/).

1. In the WordPress admin, go to **Plugins → Add New Plugin** and search for "Unomoon Form".
2. Click **Install Now**, then **Activate**.
3. Create or edit forms from the `Unomoon Form` admin menu.
4. Place the generated shortcode on a page.

To install manually instead, download `unomoon-form-<version>.zip` from the [Releases page](https://github.com/annrie/unomoon-form/releases/latest) and upload it via **Plugins → Add New Plugin → Upload Plugin**, or unzip it into `wp-content/plugins/`. Do not use the "Download ZIP" button on GitHub: that archive contains development files and its top-level directory name will not match the plugin slug.

### Post data and the "querystring" setting

A form can show values from a post with placeholders such as `{post_title}`. Without the "querystring" setting, they come from the page the form is placed on. With the setting enabled, they come from the post given by `?post_id=123`.

Since 5.1.8.2, custom fields (post meta) are not filled from the `post_id` post unless you allow their keys, because any visitor can choose the post ID. Post properties such as `{post_title}` are always available. Allow meta keys with a filter, for all forms or for one form (the form key is `unomoon-form-<form ID>`):

```php
add_filter( 'unomoonform_querystring_allowed_meta_keys', fn () => array( 'event_date', 'venue' ) );
add_filter( 'unomoonform_querystring_allowed_meta_keys_unomoon-form-123', fn () => array( 'event_date' ) );
```

Keys that start with an underscore and inquiry data are never shown, even if allowed.

### Releasing

Development happens on GitHub; the WordPress.org SVN repository is only a release target. Merging a pull request does not publish anything — pushing a `v<version>` tag runs the deploy workflow, which pushes the plugin to WordPress.org and attaches the zip to a GitHub Release. Pushes and pull requests targeting `main` or `develop` are also checked with [Plugin Check](https://wordpress.org/plugins/plugin-check/). Maintainer notes live at the top of `.github/workflows/deploy-wordpress-org.yml`.

### Upstream

This project is forked from MW WP Form: https://github.com/web-soudan/mw-wp-form

The fork exists because upstream development has stopped.

### Related plugin

Unomoon Form reCAPTCHA: https://github.com/annrie/unomoon-form-recaptcha

### License

GPLv2 or later.

## 日本語

Unomoon Form は、MW WP Form の開発停止を受けてフォークした、WordPress 7.1 検証済みのフォームプラグインです。

MW WP Form の開発が停止したため、このフォークではプラグインを `unomoon-form` 名前空間へ移行し、ショートコードベースのフォーム作成ワークフローを現在の WordPress 環境で利用できるようにしています。

### 主な機能

- ショートコードによるフォーム作成
- 確認画面
- 同一URLまたは個別URLでの画面遷移
- バリデーションルール
- 管理者宛メールと自動返信メール
- 問い合わせデータ保存
- 保存データのグラフ表示
- 日本語翻訳（translate.wordpress.org 経由）
- 外部スクリプト・スタイルの読み込みなし（Chart.js と jQuery UI テーマを同梱）

### 名前空間の変更

このフォークでは Unomoon Form の識別子を使用します。

- プラグインのスラッグ: `unomoon-form`
- 投稿タイプ: `unomoon-form`
- ショートコードの接頭辞: `unomoonform_*`
- フックの接頭辞: `unomoonform_*`
- フロントのラッパーのクラス: `.unomoon_form`

既存の MW WP Form データを利用する場合は、本番利用前に意図的に移行してください。

#### Uno WP Form（5.1.6.1 以前）からの移行

このプラグインは 5.1.6.1 まで **Uno WP Form** という名前でしたが、WordPress.org の命名規則に合わせて改名しました。すべての識別子が変わっています（`uno-wp-form` → `unomoon-form`、`unoform_*` → `unomoonform_*`、問い合わせデータの post type `uwf_*` → `unomoon_*`、meta キー `uwf_*` → `unomoonform_*`）。既存サイトは次の手順で移行してください。

1. DB をバックアップします。
2. Uno WP Form（入れていれば Uno WP Form reCAPTCHA も）を無効化し、Unomoon Form をインストールします。
3. WP-CLI で `wp eval-file tools/migrate-from-uno-wp-form.php dry-run`（位置引数）を実行して件数を確認し、`dry-run` なしでもう一度実行します。
4. Unomoon Form を有効化します。

### 動作要件

- WordPress 6.0 以上
- WordPress 7.1 検証済み
- PHP 8.0 以上

### インストール

このプラグインは [WordPress.org のプラグインディレクトリ](https://wordpress.org/plugins/unomoon-form/) で公開されています。

1. WordPress 管理画面の **プラグイン → 新規プラグインを追加** で「Unomoon Form」を検索します。
2. **今すぐインストール** → **有効化** をクリックします。
3. `Unomoon Form` の管理メニューからフォームを作成または編集します。
4. 生成されたショートコードを固定ページなどに配置します。

手動でインストールする場合は、[Releases ページ](https://github.com/annrie/unomoon-form/releases/latest) から `unomoon-form-<version>.zip` をダウンロードし、**プラグイン → 新規プラグインを追加 → プラグインのアップロード** から追加するか、`wp-content/plugins/` に展開してください。GitHub の「Download ZIP」は開発用ファイルを含み、ディレクトリ名もプラグインのスラッグと一致しないので使わないでください。

### 投稿の値と「URL 引数」の設定

フォームの本文に `{post_title}` などを書くと、投稿の値を差し込めます。「URL 引数を有効にする」がオフのときは、フォームを置いたページの値が入ります。オンのときは、`?post_id=123` で指定した投稿の値が入ります。

5.1.8.2 から、`post_id` で指定した投稿のカスタムフィールド（メタ）は、キーを許可しない限り差し込みません。投稿 ID は訪問者が自由に選べるためです。`{post_title}` などの投稿の基本の項目は、今までどおり使えます。メタのキーは、フィルターで全フォーム共通か、フォームごとに許可します（フォームキーは `unomoon-form-<フォーム ID>`）。

```php
add_filter( 'unomoonform_querystring_allowed_meta_keys', fn () => array( 'event_date', 'venue' ) );
add_filter( 'unomoonform_querystring_allowed_meta_keys_unomoon-form-123', fn () => array( 'event_date' ) );
```

`_` で始まるキーと問い合わせデータは、許可しても表示しません。

### リリース

開発は GitHub で行い、WordPress.org の SVN はリリース先としてのみ使います。プルリクエストをマージしただけでは公開されず、`v<version>` タグを push するとデプロイワークフローが WordPress.org へ配信し、zip を GitHub Release に添付します。また、`main` と `develop` への push とプルリクエストでは [Plugin Check](https://wordpress.org/plugins/plugin-check/) が実行されます。保守者向けの手順は `.github/workflows/deploy-wordpress-org.yml` の冒頭コメントにあります。

### フォーク元

このプロジェクトは MW WP Form からフォークしています: https://github.com/web-soudan/mw-wp-form

フォークした理由は、フォーク元の開発が停止したためです。

### 関連プラグイン

Unomoon Form reCAPTCHA: https://github.com/annrie/unomoon-form-recaptcha

### ライセンス

GPLv2 またはそれ以降。

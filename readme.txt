=== Unomoon Form ===
Contributors: annrie
Tags: contact form, form, confirm, mail, shortcode
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 5.1.8.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Shortcode-based contact form with a confirmation screen. A maintained fork of MW WP Form, tracking its security fixes and verified on WordPress 7.

== Description ==

Unomoon Form creates mail forms with confirmation screens using shortcodes. It is a GPL fork of MW WP Form by inc2734 / Web の相談所.

Unomoon Form は、ショートコードで確認画面付きのメールフォームを作成できます。MW WP Form の GPL フォークです。

= Why this fork exists =

The upstream project states on its official site that **development is halted except for vulnerability fixes**, that it **will not re-verify the plugin each time WordPress is updated**, and that users should consider migrating to another plugin. As of this writing, upstream is still marked "tested up to 6.4" while WordPress 7.1 has shipped.

This fork exists to do the two things upstream has stopped doing:

1. **Track upstream security fixes.** Every fix released upstream is ported here.
2. **Keep verifying against current WordPress.** Each release is tested on the latest WordPress.

Nothing about the form workflow is changed. If you are happy with MW WP Form and do not need either of the above, there is no reason to switch.

上流は公式サイトで「開発は脆弱性対応を除き停止」「WordPress のアップデートのたびに動作確認はしない」「他のプラグインへの乗り換えを検討してほしい」と表明しています。本フォークは、上流が行わなくなった **セキュリティ修正の追随** と **最新 WordPress での動作検証** を引き受けることを目的としています。フォームの作成フロー自体は変更していません。

= Differences from MW WP Form =

This fork migrates plugin identifiers, post types, shortcode prefixes, hooks, assets and admin labels to the `unomoon-form` / `unomoonform_*` namespace. **Shortcodes are not compatible**: `[mwform_text]` becomes `[unomoonform_text]`, and so on. The two plugins can therefore be installed side by side, but forms are not shared between them.

プラグイン識別子、投稿タイプ、ショートコード接頭辞、フック、アセット、管理画面ラベルを `unomoon-form` / `unomoonform_*` 名前空間へ移行しています。**ショートコードに互換性はありません。**

= Features =

* Shortcode-based form creation
* Confirmation screen
* Same-page or separate-page transitions
* Validation rules
* Admin notification email and automatic reply email
* Inquiry data storage
* Chart display for saved inquiry data
* Japanese translation available

主な機能: ショートコードによるフォーム作成／確認画面／同一URLまたは個別URLでの画面遷移／バリデーションルール／管理者宛メールと自動返信メール／問い合わせデータ保存／保存データのグラフ表示／日本語対応

= Documentation =

Japanese documentation, including how this fork differs from MW WP Form and the things people commonly trip over: https://cielos.phantomoon.com/unomoon-form/

= Credits =

Original plugin: MW WP Form by inc2734, currently maintained by Web の相談所.
Upstream repository: https://github.com/web-soudan/mw-wp-form
Upstream site: https://mw-wp-form.web-soudan.co.jp

= Third-party resources =

The following libraries are bundled with the plugin. No external scripts, styles or fonts are loaded at runtime.

* Chart.js (MIT) — renders the inquiry data charts. https://www.chartjs.org/ / https://github.com/chartjs/Chart.js
* jQuery UI "smoothness" theme CSS (MIT) — styles the date picker and admin widgets. https://jqueryui.com/ / https://github.com/jquery/jquery-ui
* jQuery UI MonthPicker (MIT) — month picker widget. https://github.com/KidSysco/jquery-ui-month-picker

== Installation ==

1. Upload the `unomoon-form` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the `Plugins` menu in WordPress.
3. Create a form from the Unomoon Form admin screen.
4. Place the generated shortcode on a page.

1. `unomoon-form` フォルダを `/wp-content/plugins/` へアップロードします。
2. 管理画面の「プラグイン」から有効化します。
3. Unomoon Form の管理画面でフォームを作成します。
4. 生成されたショートコードを固定ページなどに配置します。

== Frequently Asked Questions ==

= Can I use MW WP Form shortcodes as-is? =

No. This fork uses the `unomoonform_*` shortcode namespace. `[mwform_text]` becomes `[unomoonform_text]`, `[mwform_submitButton]` becomes `[unomoonform_submitButton]`, and so on. Hooks follow the same rule (`mwform_*` becomes `unomoonform_*`).

いいえ。`unomoonform_*` 名前空間を使用します。フックも同様です。

= Can I run this alongside MW WP Form? =

Yes. Identifiers, post types and database keys are all distinct, so the two plugins do not collide. Forms are not shared between them, so each form must be recreated.

はい。識別子・投稿タイプ・DBキーがすべて別なので衝突しません。ただしフォームは共有されないため、作り直しが必要です。

= What are the submit button attributes? =

`[unomoonform_submitButton confirm_value="..." submit_value="..."]`. One tag covers both screens: on the input screen it renders the "go to confirmation" button labelled with `confirm_value`, and on the confirmation screen it renders the send button labelled with `submit_value`.

Note that unrecognised attributes are silently ignored, and the Japanese defaults happen to read "確認画面へ" and "送信する" — so a misspelled attribute can still look correct. To place the buttons separately, use `[unomoonform_confirmButton]` and `[unomoonform_submitButton]`.

送信ボタンは `[unomoonform_submitButton confirm_value="..." submit_value="..."]` です。1つのタグが入力画面と確認画面の両方を兼ねます。**指定されていない属性は黙って無視される**ため、属性名を間違えても既定のラベルで正しく動いているように見えることがあります。

= How do I show custom fields with the "querystring" setting? =

With the setting enabled, `?post_id=123` fills placeholders such as `{post_title}` from post 123. Since 5.1.8.2, custom fields (post meta) are not filled from that post unless you allow their keys, because any visitor can choose the post ID. Allow them with a filter, for all forms or for one form:

`add_filter( 'unomoonform_querystring_allowed_meta_keys', fn () => array( 'event_date', 'venue' ) );`

`add_filter( 'unomoonform_querystring_allowed_meta_keys_unomoon-form-123', fn () => array( 'event_date' ) );`

Keys starting with an underscore and inquiry data are never shown. Without the setting, a form still shows the custom fields of the page it is placed on.

URL 引数を有効にしたフォームで、`?post_id=` で指定した投稿のカスタムフィールドを差し込むには、上のフィルターでキーを許可してください（5.1.8.2 から既定では差し込みません）。

= Where should I report issues? =

Please use GitHub Issues: https://github.com/annrie/unomoon-form/issues

== Screenshots ==

1. Form editing screen. The form body is written with shortcodes, and the auto-reply mail settings sit beside it.
2. Form tag generator. Pick a field type and it builds the shortcode for you.
3. Validation rules and admin notification mail settings.
4. Saved inquiry data. Each form field becomes a column.
5. Chart built from the saved inquiry data.

== Changelog ==

Version numbers are `<upstream version>.<fork release>`. For example 5.1.6.1 is the first fork release that has caught up with upstream 5.1.6; a fix of our own on top of it would be 5.1.6.2, and catching up with upstream 5.1.7 would be 5.1.7.1. Releases before this plugin was submitted to the directory were published on GitHub only, and used a `-uno.N` suffix (5.1.6-uno.1). The suffix was dropped because stable tags here may contain only numbers and periods.

= 5.1.8.2 =
* Security: With the "querystring" setting, custom fields (post meta) are no longer filled from the post given by `post_id` unless their keys are allowed with the new `unomoonform_querystring_allowed_meta_keys` filter (or `unomoonform_querystring_allowed_meta_keys_<form key>` for one form). Any visitor can choose the post ID, so a form placeholder such as `{email}` could read that meta key from any public post, including values stored by other plugins. Post properties such as `{post_title}` are not affected, and neither are forms without the setting, which still read the page they are placed on.
* With `WP_DEBUG` enabled, a notice reports a placeholder left empty for this reason.
* セキュリティ: 「URL 引数」がオンのフォームでは、`post_id` で指定した投稿のカスタムフィールドを、`unomoonform_querystring_allowed_meta_keys` フィルターで許可したキーだけ差し込むようにしました。投稿 ID は訪問者が選べるので、`{email}` などの記号から、ほかのプラグインが公開投稿に保存したメタまで読めました。`{post_title}` などの投稿の項目と、URL 引数がオフのフォームは今までどおりです。

= 5.1.8.1 =
* Security: Tighten access control when a form looks up a post through the `post_id` query string (the "querystring" setting). The check now requires the post to be publicly viewable, not merely in the "publish" status. Inquiry data is saved as "publish" under a non-public post type, so a form containing placeholders such as `{email}` could display another visitor's submitted values. Inquiry data post types are also rejected by name, so a filter that makes them viewable cannot reopen this. Ported from upstream 5.1.7 (re-released upstream as 5.1.8 without code changes).
* セキュリティ: 「URL 引数」で `post_id` の投稿を参照するとき、公開状態だけでなく、投稿タイプも公開かどうかを確かめるようにしました。問い合わせデータは非公開の投稿タイプに「公開」の状態で保存されるため、他人が送った内容を表示できました。上流 5.1.7 の修正の移植です。
* Behavior change: published posts of post types that are not publicly queryable (for example a custom post type registered with `public => false`) are no longer substituted through `post_id`. Placeholders for them now stay empty.

= 5.1.6.3 =
* Resolved all Plugin Check warnings. Intentional `error_log()` calls (mail delivery and upload failures) and the transient-cached direct query are now annotated with the reason; template variables set by `_render()` are marked as method-local.
* Replaced the no-op error handler around the session cookie with a `headers_sent()` guard, matching the CSRF cookie code.
* Added a plugin icon for the WordPress.org directory.

= 5.1.6.2 =
* Renamed the plugin from "Uno WP Form" to "Unomoon Form" to comply with the WordPress.org naming rules. All identifiers moved to the `unomoon-form` / `unomoonform_*` namespace: shortcodes are now `[unomoonform_*]`, hooks `unomoonform_*`, the form post type `unomoon-form`. Sites migrating from Uno WP Form must run the migration script shipped in the GitHub repository (`tools/migrate-from-uno-wp-form.php`).
* Replaced Google Charts with a bundled copy of Chart.js, and bundled the jQuery UI theme CSS. The plugin no longer loads anything from external servers.
* Security: Bound session data to a plugin-prefixed transient key and validate the session cookie format.
* Security: Sanitize all request input (nonce values, settings, inquiry data, chart settings, query strings, server variables) and escape placeholder values substituted into form content.
* Security: Use `wp_handle_upload()` for temporary file uploads and generate attachment metadata for saved files.
* Removed the mail debug log written to the uploads directory.
* Moved inline scripts and styles to `wp_add_inline_script()` / `wp_add_inline_style()`.
* Added direct-access guards to every PHP file.
* Translations are now delivered through translate.wordpress.org instead of bundled files.

= 5.1.6.1 =
* Changed the version numbering scheme, dropping the `-uno.N` suffix. Same code as 5.1.6-uno.1 on GitHub.
* Security: Neutralize shortcode syntax in values rendered on the completion screen. A shortcode typed into a form field was executed when the completion screen substituted the value. Ported from upstream 5.1.5.
* Security: Tighten output escaping on the inquiry data list screen. Meta keys derive from mail-content tags and were printed verbatim into HTML id / class attributes and the Screen Options panel. Column identifiers are now mapped to safe slugs. Ported from upstream 5.1.5 and 5.1.6.
* Security: Add output escaping to the dynamic column labels, `response_status` and `admin_mail_to`. Ported from upstream 5.1.5.
* Tested on WordPress 7.1.

= 5.1.4.1 =
* Initial release of the fork, based on MW WP Form 5.1.4.
* Migrated identifiers, post types, shortcode prefixes, hooks, assets and admin labels to the `uno-wp-form` / `unoform_*` namespace (renamed again to `unomoon-form` / `unomoonform_*` in 5.1.6.2).

== Upgrade Notice ==

= 5.1.8.2 =
Security hardening. Forms with the "querystring" setting no longer fill custom fields from the post given by `post_id` unless the keys are allowed with the `unomoonform_querystring_allowed_meta_keys` filter. See the FAQ.

= 5.1.8.1 =
Security release. If a form has the "querystring" setting enabled, its placeholders could display submitted inquiry data looked up by `post_id`. Updating is recommended. Posts of non-public post types are no longer substituted through `post_id`.

= 5.1.6.2 =
The plugin was renamed to Unomoon Form and every identifier changed. If you are upgrading from Uno WP Form, back up your database and run the migration script from the GitHub repository before activating this version. Also a security hardening release.

= 5.1.6.1 =
Security release. Fixes shortcode execution on the completion screen, which is reachable by unauthenticated visitors, and tightens output escaping on the inquiry data list screen. Updating is recommended.

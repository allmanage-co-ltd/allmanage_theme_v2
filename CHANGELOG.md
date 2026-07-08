# CHANGELOG

---

## [Unreleased] - 2026-07-08 (2)

### Added
- `app/Presenters/Metadata.php`: `getGtmHead()` / `getGtmBody()` を追加。`config/seo.gtm` のラベルキー付き連想配列から head/body スニペットを出力。ローカル環境はスキップ
- `app/Hooks/AddHeadMetadata.php`: `wp_body_open` アクションに `addGtmBody()` を追加
- `views/layout/header.php`: `<body>` 直後に `wp_body_open()` 呼び出しを追加

### Changed
- `app/Presenters/Metadata.php`: `getGtags()` をラベルキー付き連想配列・複数 ID 対応に修正（`['label' => ['G-XXXX', 'G-YYYY']]` 形式）。ローカル環境スキップを追加
- `config/seo.php`: `gtags` / `gtm` をラベルキー付き連想配列に変更（例: `'allmanage' => [...]`）。GTM サンプルスニペットを追加

## [Unreleased] - 2026-07-08

### Added
- `app/Services/Csv/ImportCsvAbstract.php`: Ajax リクエスト時に ndjson ストリームで進捗を逐次返す `handleStreaming()` を追加。1行処理ごとに `{"processed":N,"total":M,...}` を出力し、完了時に `{"done":true,...}` を送信
- `app/Services/Csv/ExportCsvAbstract.php`: `filenameParam()` を追加。エクスポートファイル名を GET パラメータで指定できるように変更
- `views/app/admin/csv-in-expoter.php`: インポート進捗バー UI（`#csv-progress`）を追加。Ajax ストリームを受信してリアルタイムに進捗表示するスクリプトを追加
- `views/app/admin/csv-in-expoter.php`: エクスポートフォームにファイル名入力欄を追加。投稿タイプ切替時にデフォルト値を自動更新
- `config/recaptcha.php`: Turnstile 設定ファイルを新規追加

### Changed
- `app/Services/Csv/ImportCsvAbstract.php`: `handle()` を Ajax 対応に拡張。`X-Requested-With: XMLHttpRequest` ヘッダーがあればストリーミングモードで処理
- `app/Services/Csv/ExportCsvAbstract.php`: `handle()` でファイル名を `filenameParam()` の GET 値から取得するよう変更。インデントを4スペースに統一
- `app/Services/Csv/CsvWriter.php`: インデントを4スペースに統一（動作変更なし）
- `app/Services/Csv/CsvReader.php`: インデントを4スペースに統一（動作変更なし）
- `app/Services/Csv/Actions/ImportRunAction.php`: `onProgress` コールバックを受け取れるよう拡張。各行処理後にコールバックを呼び出す
- `app/Services/Csv/Actions/ImportPostSaveAction.php`: インデントを4スペースに統一（動作変更なし）
- `app/Services/Csv/Actions/ImportValueConvertAction.php`: インデントを4スペースに統一（動作変更なし）
- `app/Services/Csv/Actions/ImportAttachmentResolveAction.php`: インデントを4スペースに統一（動作変更なし）
- `app/Services/Csv/Actions/ImportColumnAction.php`: インデントを4スペースに統一（動作変更なし）
- `app/Services/Csv/Actions/ExportGetTermSlugsAction.php`: インデントを4スペースに統一（動作変更なし）
- `app/Plugins/MwFormHook.php`: `use_add_turnstile` が `true` のとき Cloudflare Turnstile ウィジェットをフォーム末尾に挿入する処理を追加。郵便番号フィールドに `〒` プレフィックスを追加
- `app/Project/ExportNewsCsv.php`: `get_fields()` を `get_post_meta()` に変更（ACF 依存を除去）。`setPostStatus('any')` を追加して全ステータスの投稿を対象に
- `app/Services/Query/MyWpQuery.php`: `setPostStatus()` メソッドを追加
- `app/Hooks/ImportCsvHook.php`: Ajax リクエスト時は `handle()` 内で `exit` するため到達しない旨のコメントを追加
- `views/app/admin/csv-in-expoter.php`: エクスポートの `<option>` に `esc_attr` / `esc_html` を適用

## [Unreleased] - 2026-06-25

### Added
- `app/Services/Query/MyWpQuery.php`: `setPostNotIn` / `setDateAfter` / `setDateBefore` / `setDateBetween` / `setTaxRelation` / `setMetaRelation` メソッドを追加
- `app/Services/Query/MyWpQuery.php`: `forArchive` / `forTaxArchive` ファクトリメソッドを追加
- `bootstrap/functions.php`: `wpquery_archive` / `wpquery_tax` / `get_post_term` グローバル関数を追加
- `views/taxonomy/news_tag.php`: タグアーカイブテンプレートを新規追加

### Changed
- `bootstrap/hooks.php`: サンプルをタイトル省略フィルター（`the_title`）のコメント例に更新
- `views/archive/news.php`: `wpquery_archive` ショートハンドに移行、変数名を `$news_query` に統一
- `views/taxonomy/news_cat.php`: `wpquery_tax` ショートハンドに移行、変数名を `$news_query` に統一
- `views/component/news/c-card_news.php`: `$query` → `$news_query` に変数名統一、`get_post_term` ヘルパーを使うよう簡略化
- `views/single/news.php`: `get_post_term` ヘルパーを使うよう簡略化
- `views/page/search.php`: `wpquery_archive` ショートハンドに移行
- `CLAUDE.md`: CHANGELOG 記録ルールを追記

## [Unreleased] - 2026-06-24

### Added
- `bootstrap/hooks.php`: クラス化するほどでもない小さな Hook の仮置き場を新規追加。`bootstrap/app.php` から自動読み込み
- `config/plugin.php`: 必須プラグイン一覧設定ファイルを新規追加（SiteGuard / WPvivid / AIOSEO / MW WP Form / ACF / Post Types Order / Taxonomy Terms Order）
- `app/Hooks/SetupTheme.php`: `adminNoticeRequiredPlugins` フックを追加（未インストールの必須プラグインを管理画面に通知）

### Changed
- `bootstrap/functions.php`: コメントブロックの閉じタグ修正（`/` → `*/`）。案件ごとの追加関数セクションに「上から追加してください」の説明を追記
- `views/component/searchform.php`: `post_type` を `$args['post_type']` から取得できるよう対応（呼び出し元から上書き可能に）
- `views/layout/sidebar.php`: サイドバーカテゴリーの見出しテキストを「経営課題から探す」→「カテゴリー」に変更
- `app/Hooks/SetupTheme.php`: インデントを4スペースから2スペースに統一
- `config/cms.php`: `csv-in-exporter` オプションページのコメントブロック追加・プロパティ順序を整理。`news` 投稿タイプの `show_in_rest` を `true` に変更。インデントを2スペースに統一
- `config/searchform.php`: `news` フィルターに `acf_is_public` / `acf_check` / `acf_price` のカスタムフィールドキーを追加。インデントを2スペースに統一
- `views/layout/footer.php`: HTML 構造をリファクタリング（会社情報・住所・電話番号ブロックを追加）。クッキーモーダルをコメントアウト。コンタクトページ判定ロジックを修正（否定条件に変更）
- `views/page/search.php`: 投稿タイプ別の `switch` 文でコンポーネントを切り替える対応を追加
- `assets/img/common/symbol-defs.svg`: `icon-arrow` SVG パスを更新
- `.vscode/settings.json`: インデントを4スペースから2スペースに統一。ファイルネストパターンを調整
- `assets/img/home/.keep`: `.gitkeep` にリネーム

### Changed（2026-06-17）
- `MwFormHook.php`: フッタースクリプトのページ判定ロジックを `config/mwform.php` の設定値へ委譲（`Config::get('mwform.foot-script')` 経由）。ハードコードを廃止
- `MwFormHook.php`: バリデーション・自動返信メールのサンプルコードをコメントアウト（案件実装前の誤発火防止）
- `MwFormHook.php`: 自動返信メール本文に「ご来社日時 `{your_date}`」フィールドを追加
- `bootstrap/functions.php`: 案件ごとの追加関数セクションを明示するコメントブロックを追加
- `views/component/searchform.php`: 検索ボタンの `<img>` タグをインライン SVG に置き換え（外部ファイル参照を廃止）
- `.claude/settings.json`: 末尾カンマ（trailing comma）を2か所修正し、JSON として valid な状態に修正

---

## [2026-06-17] - ec3516c

### Changed
- `assets/scss/layout/_breadcrumb.scss`: パンくずのスタイルを調整
- `assets/scss/object/_wp.scss`: WP エディタ向けスタイルを大幅リファクタリング
- `config/searchform.php`: 検索フォーム設定を更新
- `views/component/searchform.php`: 検索フォームコンポーネントを更新
- `views/layout/footer-navi.php`: フッターナビを新規追加
- `views/layout/footer.php`: フッターレイアウトを更新
- `views/layout/header-navi.php`: ヘッダーナビを更新
- `views/layout/sidebar.php`: サイドバーテンプレートを追加
- `views/page/404.php`: 404ページを更新
- `views/page/search.php`: 検索結果ページを更新
- `views/taxonomy/news_cat.php`: ニュースカテゴリーアーカイブを更新
- `app/Plugins/MwFormHook.php`: MW WP Form 関連フックを更新

---

## [2026-06-10] - a8c564b

### Added
- `.claude/rules/` 以下に各種ルールファイルを追加（persona, output-format, security, session-start, token-efficiency, git-workflow, codex, gemini, code-styles）
- `.claude/settings.json` を新規追加（パーミッション設定・フック・プラグイン設定）
- `CLAUDE.md` を更新（ルールファイルを `@` インポート形式に変更）

### Changed
- `app/Hooks/ExportCsvHook.php` / `ImportCsvHook.php`: 軽微な修正
- `README.md`: 内容を更新

---

## [2026-04-27] - ac25a8d

### Fixed
- `app/Presenters/Metadata.php`: GA4 トラッキングコードが複数出力されるバグを修正

---

## [2026-04-24] - dd8a798

### Added
- `CLAUDE.md`: Claude Code 向けの作業ルール・アーキテクチャ概要ドキュメントを追加

---

## [2026-04-24] - 812febc

### Removed
- `Pest` / `PHPUnit` を依存から削除（テストフレームワーク一式を除去）

---

### 2026.4.24

- `config/menu.php` に AIOSEO ダッシュボードウィジェット非表示設定を追加
- `RegisterOptionPage.php` に `redirect` / `icon` / `position` 設定を追加
- `SavePostTaxonomyRequired.php` のエラー処理を修正（auto-draft・リビジョン・ゴミ箱時はスキップ）

---

### 2026.3.24

- `app` ディレクトリの構造を大幅変更（WP依存を整理、保守性重視の設計に移行）
- `config` に新設定項目を追加（`acf`, `cms.taxonomy_required`, `logger.*`, `searchform.filter`）
- `views` 内を整理（NEWSカードコンポーネント化）
- `get_acf_action` を `get_acf_fields` にリネーム・仕様変更

---

### 2026.3.15

- `views/` ドキュメントを追加
- `scss` 記載ルールの文言を修正
- `img_dir` 関数を `img_uri` にリネーム
- `MwWpForm.php` の複数バグ修正（フック・JS タイポ・自動返信初期値）
- `views/contact/index.php` に reCAPTCHA 対応コメントを追加
- `config/menu.php` を `cms.php` から分割
- `seo.php` に `use_all_in_one_seo` 項目を追加
- `the_breadcrumb` 関数の出力 HTML を変更（`l-breadcrumb` クラス追加）
- `the_postnavi` 関数を追加（前後記事・一覧ナビ出力）
- `get_acf_action` 関数を追加（ACF一括取得・DBアクセス最小化）

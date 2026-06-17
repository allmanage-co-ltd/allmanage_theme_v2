# CHANGELOG

---

## [Unreleased] - 2026-06-17

### Changed
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

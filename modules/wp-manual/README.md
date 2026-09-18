# 管理画面マニュアル 自動生成モジュール（仮実装）

PHP 7.4 / WordPress 5.3 以上。ACF は任意（無ければ標準項目だけで生成）。

## 何をするか

サイトの構成を実行時に解析し、管理画面の「マニュアル」メニューに操作手順を描画する。
フロントには一切出力しない（管理画面内で完結）。

解析対象：
- 管理画面に表示される投稿タイプ（固定ページ含む。標準の「投稿」は既定で除外）と対応機能（本文/アイキャッチ/抜粋）
- エディタ種別（ブロック / クラシック）
- タクソノミー（階層型 / タグ型）
- ACF フィールドグループ（location で投稿タイプ・オプションページに紐付くもの）
  - repeater / group / flexible_content のサブフィールドも階層表示
  - `instructions` はそのまま「入力方法・ルール」に載る
- ACF オプションページ
- ナビゲーションメニューの位置と割り当て
- Welcart の有無、MW WP Form の問い合わせ履歴（`mwf_*`）
- ACF の location ルールを自前で解析（`post_type` / `options_page` / `page` / `post` / `page_type` /
  `page_template` / `page_parent` / `post_taxonomy` / `post_category` / `post_format` / `post_status`）
  - 特定の固定ページに紐付くグループは「そのページ専用」セクションとして独立
  - テンプレートや分類による条件は「〜の場合のみ表示」として表に載る
- フィールドの入力制約（文字数・数値範囲・ファイル形式/容量/寸法・件数・初期値）を自動転記
- ACF の条件判定（conditional_logic）を「〜のとき表示されます」と文章化
- 管理者には `instructions` が空のフィールドを「説明が未記入」として表示（納品前チェック用）

## 導入

1. `inc/wp-manual/` としてテーマ内に配置
2. `functions.php` に追記

```php
require_once get_template_directory() . '/inc/wp-manual/wp-manual.php';
```

3. 管理画面に「マニュアル」メニューが出る（管理バーにも表示）

## 権限

| 定数 | 既定 | できること |
|---|---|---|
| `WP_MANUAL_VIEW_CAP` | `edit_posts` | 閲覧、補足メモの編集、FAQ の追加・編集・削除（編集者以上） |
| `WP_MANUAL_ADMIN_CAP` | `manage_options` | 上記に加え、項目ごとの表示 / 非表示切り替え（管理者） |

## 表示 / 非表示（管理者）

各項目の右上「この項目を非表示にする / 表示する」で切り替え。非表示中の項目は
- 管理者：タブに目のアイコン＋取り消し線、パネル上部に黄色の帯で「非表示中」と表示
- 管理者以外：タブにも本文にも一切表示されない

**デフォルトで非表示（固定）**：「固定ページ」と「特定ページ専用」セクション。管理者が「表示する」を押すまで編集者には出ない。
「閲覧ログ」は管理者以外には生成されない。

`wp_options` の `wp_manual_visibility` に `ID => 'visible' | 'hidden'` で保存（明示的に切り替えたもののみ）。
旧形式 `wp_manual_visibility` があれば初回表示時に自動移行。

## 補足メモ（編集者以上）

自動で拾えない運用ルール（更新頻度、画像の用意担当など）を各項目の末尾から保存。
空で保存すると削除。`wp_options` の `wp_manual_notes` に保存。

## FAQ「困ったときは」（編集者以上）

各質問の「この質問を編集」で更新・削除、末尾の「質問を追加」で追加。
初回表示時に初期5問が `wp_options` の `wp_manual_faq` に保存され、以降は編集内容がそのまま残る。

## 検索

左上の検索ボックスでタブ名＋本文をインクリメンタル検索し、該当するタブだけを表示する。
全角英数は半角に寄せて照合。Esc でクリア。

## 「今いる画面のマニュアル」導線

- 管理バーの「マニュアル」は、現在の画面（投稿一覧・編集・オプションページ・メニュー・メディア）に
  対応するタブへ直接飛ぶ（別タブで開く）。対応があるときはラベルが「この画面のマニュアル」になる
- 投稿編集画面の右サイドに「この画面の使い方」メタボックスを表示
- 判定用の対象一覧は transient に10分キャッシュ。ACF 保存・投稿タイプ登録時に自動で破棄

## 閲覧ログ（管理者）

タブを開くたびに JS から `sendBeacon` で記録（1ページ表示中は同じタブ1回のみ）。
テーブル `{prefix}wp_manual_views` に保存し、「管理者向け」グループの「閲覧ログ」タブで
項目別（30日 / うち編集者 / 累計 / 最終閲覧）と直近20件を表示。365日で自動削除。

## 問い合わせ履歴（MW WP Form）

`post_type=mwf_{フォームID}` は MW WP Form の受信データ保存用投稿タイプなので、
通常のカスタム投稿とは分けて「お問い合わせ履歴」グループに出し、閲覧と CSV ダウンロードの手順を説明する。

## カスタマイズ

```php
// マニュアルに載せない投稿タイプを追加 / 標準の「投稿」を載せる
add_filter('wp_manual/exclude_post_types', function ($excluded) {
    $excluded[] = 'dev_note';
    return array_values(array_diff($excluded, ['post']));
});

// フィールド型の説明文を差し替え
add_filter('wp_manual/field_type_guide', function ($guide, $type) {
    if ($type === 'image') {
        $guide['howto'] .= ' 横1200px以上のJPGを推奨します。';
    }
    return $guide;
}, 10, 2);

// セクションの追加・並び替え・削除
add_filter('wp_manual/sections', function ($sections, $info) {
    $sections[] = ['id' => 'rules', 'title' => '運用ルール', 'group' => 'basic', 'icon' => 'dashicons-clipboard', 'hidden' => false, 'html' => '<p>毎月10日までに新着を更新</p>'];
    return $sections;
}, 10, 2);
```

## 撤去

1. `functions.php` の `require_once` を削除
2. フォルダを削除
3. 必要なら `wp_options` の `wp_manual_notes` / `wp_manual_visibility` / `wp_manual_faq` / `wp_manual_db_version` と
   transient `wp_manual_targets` を削除
4. 閲覧ログのテーブルを削除：`DROP TABLE wp_wp_manual_views;`（または `(new \WpManual\Log())->drop()`）

## ファイル構成

```
wp-manual/
├── wp-manual.php            エントリ・定数
├── class-inspector.php      サイト構成の解析
├── class-renderer.php       解析結果 → HTML（フィールド型ごとの説明文はここ）
├── class-admin.php          メニュー・導線・各種保存・ログ受信
├── class-log.php            閲覧ログのテーブル操作
├── templates/
│   └── manual-page.php      目次 + 本文レイアウト
└── assets/
    ├── manual.css           画面・印刷スタイル
    └── manual.js            目次のカレント表示
```

## 今後の拡張候補

- 解析結果を JSON で出力（管理者限定）→ 既存の PPTX 生成スクリプトへ供給
- Smart Custom Fields / WooCommerce のフィールド解析
- セクションごとの「よく使う操作」動画リンク欄

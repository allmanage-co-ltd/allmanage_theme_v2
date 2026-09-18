# modules/ — 便利機能モジュール集

このディレクトリは **AI を活用して試験的に作成した WordPress 便利機能のモジュール集**です。  
テーマ本体のアーキテクチャとは独立しており、旧テーマも含めた様々な WordPress テーマで再利用できます。

---

## 設計思想

| 原則 | 内容 |
|---|---|
| **自己完結** | クラス・関数・アセットはすべてモジュールディレクトリ内に閉じる。`app/` に依存しない |
| **PHP 7.4 対応** | union 型・`readonly`・名前付き引数などの PHP 8 構文は使わない |
| **include 一本** | エントリファイル（`{slug}/{slug}.php`）を require するだけで動く |
| **WordPress 依存のみ** | Composer パッケージへの依存は持たない |
| **フロントに出力しない** | 管理画面専用機能はフロントに一切出力しないよう設計する |

> クラスを使ってよい。ただし namespace はモジュール内に閉じた短い名前（例: `WpManual`）にし、`App\` 名前空間を使わない。

---

## 読み込み方

`bootstrap/functions.php`（またはテーマの `functions.php`）から **1 行だけ**インクルードします。

```php
require_once get_template_directory() . '/modules/index.php';
```

個々のモジュールを直接 require するのではなく、`modules/index.php` を経由してください。

---

## 機能の ON / OFF

`modules/index.php` を開き、不要な行をコメントアウトするだけで無効化できます。

```php
// 有効
require_once __DIR__ . '/wp-manual/wp-manual.php';

// 無効にする場合はコメントアウト
// require_once __DIR__ . '/another-feature/another-feature.php';
```

---

## ディレクトリ構成

```
modules/
├── index.php            ← モジュールの ON/OFF はここで管理
├── README.md            ← このファイル
│
├── wp-manual/           ← 管理画面マニュアル自動生成（参考実装）
│   ├── wp-manual.php    ← エントリ・定数定義
│   ├── class-*.php      ← 機能クラス（モジュール内に閉じた namespace）
│   ├── templates/       ← PHP テンプレート（HTML 描画）
│   ├── assets/          ← CSS / JS
│   └── README.md        ← モジュール固有の説明
│
└── {slug}/              ← 新規モジュールはここに追加
    ├── {slug}.php       ← エントリファイル（必須）
    └── README.md        ← モジュール説明（必須）
```

---

## 新規モジュールの作り方

### 1. ディレクトリ・ファイルを作成

モジュール名はハイフン区切り（kebab-case）のスラッグで統一します。

```
modules/{slug}/
├── {slug}.php           ← エントリ（定数定義・クラスの require・フック登録）
├── class-{name}.php     ← クラス（複数可）
├── templates/           ← PHP テンプレート（あれば）
├── assets/              ← CSS / JS（あれば）
└── README.md            ← 説明書（必須）
```

**例：`my-feature` というモジュールを作る場合**

```
modules/my-feature/
├── my-feature.php
├── class-admin.php
├── templates/my-feature-page.php
├── assets/my-feature.css
├── assets/my-feature.js
└── README.md
```

---

### 2. ファイル命名規則

| ファイル | 命名パターン | 例 |
|---|---|---|
| エントリ | `{slug}.php` | `wp-manual.php` |
| クラス | `class-{role}.php` | `class-admin.php`, `class-renderer.php` |
| テンプレート | `{slug}-{name}.php` または `{name}.php` | `manual-page.php` |
| CSS | `{slug}.css` または `{name}.css` | `manual.css` |
| JS | `{slug}.js` または `{name}.js` | `manual.js` |

---

### 3. エントリファイルの書き方

`{slug}/{slug}.php` はモジュールの玄関口です。以下の順番で記述します。

```php
<?php

/**
 * モジュール名 — 1行で何をするか説明
 *
 * 概要・依存プラグイン・対応バージョン等をここに書く
 */

namespace MyFeature;           // モジュール内に閉じた短い namespace

if (!defined('ABSPATH')) {
    exit;
}

// 定数（すべてモジュールプレフィックス付き）
define('MY_FEATURE_DIR', __DIR__);
define('MY_FEATURE_URL', (function () {
    $theme_dir = wp_normalize_path(get_stylesheet_directory());
    $this_dir  = wp_normalize_path(__DIR__);
    if (strpos($this_dir, $theme_dir) === 0) {
        return get_stylesheet_directory_uri() . substr($this_dir, strlen($theme_dir));
    }
    return get_template_directory_uri() . '/modules/my-feature';
})());
define('MY_FEATURE_MENU_SLUG', 'my-feature');

// クラスファイルの読み込み
require_once MY_FEATURE_DIR . '/class-admin.php';

// WordPress フックへの登録
add_action('after_setup_theme', function () {
    if (is_admin()) {
        Admin::instance();
    }
});
```

**定数の命名：** `UPPER_SNAKE_CASE` でモジュールスラッグをプレフィックスにします。

```php
// 良い例（プレフィックスあり）
define('WP_MANUAL_DIR', __DIR__);
define('MY_FEATURE_MENU_SLUG', 'my-feature');

// 悪い例（短すぎて衝突リスクあり）
define('DIR', __DIR__);
define('MENU_SLUG', 'my-feature');
```

---

### 4. クラスの書き方

**namespace** はモジュールに閉じた PascalCase の短い名前にします（`App\` 名前空間は使わない）。

```php
<?php

/**
 * MyFeature — 管理画面ページ
 *
 * - 何をするクラスか
 * - 依存するクラス・定数
 */

namespace MyFeature;

if (!defined('ABSPATH')) {
    exit;
}

class Admin
{
    /** @var self|null */
    private static $instance = null;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('admin_menu', [$this, 'addMenu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
    }

    public function addMenu(): void
    {
        add_menu_page(
            'My Feature',
            'My Feature',
            'edit_posts',
            MY_FEATURE_MENU_SLUG,
            [$this, 'render'],
            'dashicons-admin-generic',
            80
        );
    }

    public function enqueue(string $hook): void
    {
        if ($hook !== 'toplevel_page_' . MY_FEATURE_MENU_SLUG) {
            return;
        }
        wp_enqueue_style(
            'my-feature',
            MY_FEATURE_URL . '/assets/my-feature.css',
            [],
            (string) filemtime(MY_FEATURE_DIR . '/assets/my-feature.css')
        );
    }

    public function render(): void
    {
        if (!current_user_can('edit_posts')) {
            return;
        }
        // テンプレートに変数を渡して include
        $data = ['title' => 'My Feature'];
        include MY_FEATURE_DIR . '/templates/my-feature-page.php';
    }
}
```

#### PHP 7.4 で使えない構文（使用禁止）

```php
// NG: union 型（PHP 8.0+）
public function foo(string|array $val): void {}

// NG: readonly プロパティ（PHP 8.1+）
public readonly string $name;

// NG: 名前付き引数（PHP 8.0+）
array_slice(array: $arr, offset: 0);

// NG: match 式（PHP 8.0+）
$result = match ($x) { 1 => 'a', default => 'b' };

// NG: nullsafe 演算子（PHP 8.0+）
$val = $obj?->method();

// NG: アロー関数（PHP 7.4 で使用可能・OK）
$fn = fn($x) => $x * 2;  // これは OK

// OK: 型ヒント・戻り値型（PHP 7.0+）
public function foo(string $key, array $data = []): bool {}

// OK: null 合体演算子（PHP 7.0+）
$val = $_GET['key'] ?? 'default';

// OK: スプレッド演算子（PHP 5.6+）
$merged = array_merge(...$arrays);
```

---

### 5. wp_options / DB の使い方

WordPress の標準 API を使います。キーはモジュールスラッグをプレフィックスにして衝突を防ぎます。

```php
// オプション保存（autoload=false を推奨）
update_option('my_feature_settings', $data, false);
$data = get_option('my_feature_settings', []);

// Transient（一時キャッシュ）
set_transient('my_feature_cache', $data, 10 * MINUTE_IN_SECONDS);
$data = get_transient('my_feature_cache');
delete_transient('my_feature_cache');
```

カスタムテーブルが必要な場合は `dbDelta()` で作成し、バージョン管理をオプションで行います（`wp-manual/class-log.php` 参照）。

---

### 6. セキュリティチェックリスト

モジュールを作る前に以下を確認します。

- [ ] `if (!defined('ABSPATH')) { exit; }` をすべてのファイルに記述
- [ ] フォーム送信には `wp_nonce_field()` / `check_admin_referer()` で CSRF 対策
- [ ] Ajax には `check_ajax_referer()` で検証
- [ ] `current_user_can()` で権限チェックを処理の先頭で行う
- [ ] `$_GET` / `$_POST` は `sanitize_text_field()` / `absint()` 等でサニタイズ
- [ ] HTML 出力は `esc_html()` / `esc_attr()` / `esc_url()` でエスケープ
- [ ] SQL は `$wpdb->prepare()` を使い文字列結合しない

---

### 7. README.md の書き方

各モジュールに README を置きます。以下のセクションを含めてください。

```markdown
# モジュール名 — 一言で何をするか

## 何をするか
このモジュールが解決する課題と主な機能。

## 依存
- WordPress バージョン
- 必要なプラグイン（任意）

## 導入
インストール手順。`modules/index.php` への追記方法。

## 権限
利用に必要な WordPress 権限（`edit_posts` / `manage_options` 等）。

## カスタマイズ
wp_filter や wp_action のフックポイント（あれば）。

## 撤去
削除手順（wp_options や DB テーブルの掃除方法を含む）。

## ファイル構成
ディレクトリツリーと各ファイルの役割。
```

---

## 参考実装：wp-manual

`wp-manual/` が現時点での唯一の実装例です。新規モジュール作成時の参考にしてください。

| ファイル | 役割 |
|---|---|
| `wp-manual.php` | 定数定義・クラスの require・`after_setup_theme` でのフック登録 |
| `class-inspector.php` | サイト構成（投稿タイプ / ACF / メニュー等）をランタイムに解析 |
| `class-renderer.php` | 解析結果を HTML に変換。フィールド型ごとの説明文もここ |
| `class-admin.php` | 管理メニュー追加・アセット読み込み・フォーム保存・ログ受信 |
| `class-log.php` | 閲覧ログのカスタムテーブル操作（作成・記録・集計・削除） |
| `templates/manual-page.php` | 目次＋本文の HTML レイアウト。ロジックは持たず描画専用 |
| `assets/manual.css` | 管理画面専用スタイル |
| `assets/manual.js` | タブ・検索などのインタラクション |

---

## 現在登録されているモジュール

| スラッグ | 説明 | 状態 |
|---|---|---|
| `wp-manual` | 管理画面マニュアル自動生成 | 有効 |
| `env-badge` | 管理バーに環境バッジ表示（ローカル/ステージング/本番） | 有効 |
| `query-monitor-lite` | ローカル/ステージング限定でクエリ数・遅いクエリを画面下部に表示 | 有効 |

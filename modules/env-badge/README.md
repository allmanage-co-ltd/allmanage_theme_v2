# env-badge — 環境バッジ表示

管理バーに **「ローカル」「ステージング」** バッジを色付きで表示し、誤った環境での操作を防ぐ。  
本番環境では何も表示しない。

PHP 7.4 以上 / WordPress 5.0 以上。外部依存なし。

## 表示例

| 環境 | バッジ | 色 |
|---|---|---|
| ローカル | `ローカル` | 緑 |
| ステージング | `ステージング` | オレンジ |
| 本番 | 非表示 | — |

## 環境判定ロジック

ホスト名の部分一致で判定します（ローカル → ステージング → 本番の順）。

**ローカルと判定するホスト（デフォルト）**
- `localhost`, `127.0.0.1`, `.local`, `.test`, `web-checker`

**ステージングと判定するホスト（デフォルト）**
- `.check-xserver.jp`, `staging.`, `stg.`, `dev.`, `preview.`

## カスタマイズ

### 判定結果を直接上書き

`WP_ENV` 定数など、別の仕組みで環境を管理している場合はこちら。

```php
add_filter('env_badge/detect', function ($env) {
    if (defined('WP_ENV')) {
        return WP_ENV; // 'local' | 'staging' | 'production'
    }
    return $env;
});
```

### ステージングのホスト条件を追加

```php
add_filter('env_badge/staging_hosts', function ($hosts) {
    $hosts[] = 'myproject.example.com';
    return $hosts;
});
```

### ローカルのホスト条件を追加

```php
add_filter('env_badge/local_hosts', function ($hosts) {
    $hosts[] = 'myproject.lndo.site';
    return $hosts;
});
```

## 導入

`modules/index.php` に1行追加するだけです。

```php
require_once __DIR__ . '/env-badge/env-badge.php';
```

## 撤去

1. `modules/index.php` の該当行をコメントアウトまたは削除
2. `modules/env-badge/` ディレクトリを削除
3. wp_options や DB テーブルは使用していないため、クリーンアップ不要

## ファイル構成

```
env-badge/
├── env-badge.php      エントリ・定数定義・クラスのrequire・フック登録
├── class-badge.php    環境判定・管理バーノード追加・インラインスタイル出力
└── README.md          このファイル
```

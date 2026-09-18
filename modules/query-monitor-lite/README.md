# query-monitor-lite — クエリモニター

ローカル・ステージング環境限定で画面下部に固定バーを表示する開発者向けモジュール。  
クエリ数・合計時間・遅いクエリをページを離れずにその場で確認できる。

PHP 7.4 以上 / WordPress 5.0 以上。外部依存なし。

## 表示内容

| 項目 | 説明 |
|---|---|
| queries | 総クエリ数 |
| 合計時間 | 全クエリの合計実行時間（ms） |
| ⚠ slow | 閾値（デフォルト 50ms）を超えたクエリ数 |

クリックで展開すると遅いクエリ一覧・全クエリ一覧を表示（`SAVEQUERIES=true` が必要）。  
遅いクエリはオレンジ太字で強調表示され、📋 ボタンで SQL をクリップボードにコピーできる。

## 詳細表示を有効にする

`wp-config.php` に追記：

```php
define('SAVEQUERIES', true);
```

このモジュールを有効にすると `SAVEQUERIES` が未定義の場合は自動で `true` にする。  
ただし本番環境では動作しないため、本番への影響はない。

## カスタマイズ

```php
// 遅いクエリの閾値を変更（デフォルト 50ms）
define('QML_SLOW_THRESHOLD', 0.1); // 100ms

// 常に表示（本番でも出したい場合）
add_filter('qml/enabled', function ($enabled) {
    return true;
});
```

## 導入

`modules/index.php` に1行追加：

```php
require_once __DIR__ . '/query-monitor-lite/query-monitor-lite.php';
```

## 撤去

1. `modules/index.php` の該当行をコメントアウトまたは削除
2. `modules/query-monitor-lite/` ディレクトリを削除

## ファイル構成

```
query-monitor-lite/
├── query-monitor-lite.php   エントリ・定数定義・SAVEQUERIES の強制有効化
├── class-monitor.php        環境判定・クエリ集計・HTML 出力
└── README.md                このファイル
```

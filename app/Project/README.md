# UseCase ディレクトリガイド

### **※案件ごとの専用クラスなので WP 依存、使い捨て OK です**

`UseCase/` は、**案件ごとに内容が変わる処理の実装を置く層**です。
大きく 2 つの用途で使います。

---

## 1. 基盤クラスの案件固有実装

`CMS/` や `Support/` で定義された抽象クラスを継承して、
案件ごとに異なる部分（記録内容・対象投稿タイプ・カラム定義など）を実装します。

### 例

```php
// App\CMS\Hooks\AccessLogAbstract を継承
// 案件ごとに記録する内容・除外条件・フックを実装する
final class RequestAccessLog extends AccessLogAbstract { ... }

// App\CMS\Admin\EditPostColumns を継承
// 案件ごとに対象投稿タイプ・カラム定義・値の出力を実装する
final class EditNewsPostColumnsAction extends EditPostColumns { ... }
```

---

## 2. 案件固有の使い捨て処理

汎用化するほどでもない、案件特有のロジックをゴリっと書く場所です。
設計より実装速度を優先して OK です。

### 例

```php
// 畳の商品情報をCSVでダウンロードする
final class ExportTatamiCsv {
    public function __invoke(): void {
        // ここに処理を書く
    }
}
```

- `ImportProductsCsv`
- `ExportOrdersCsv`
- `GenerateInvoicePdf`
- `SendWebhookNotification`
- `SyncExternalApi`

---

## ルール

- WP 依存・使い捨て OK
- WP フックを継承する場合は`bootstrap/app.php` でインスタンス化して呼び出す
- 汎用化できそうで頻出するだろう処理は`Support/` に切り出すことを検討する

# Actions ディレクトリガイド

`Actions/` は、**ユースケース単位の処理を組み立てる層**です。
特定のクライアントのユースケースに応じて、使用してください。
※`Services`で定義された基盤ロジックを使って、用途に応じた具体的な何かを行う。

例えば、畳の商品情報をカスタム投稿で管理しており
そのCSV一括取得を行うロジックを書く必要がある場合など。

```php
<?php
class ExportTatamiCSV {
    public function __invoke(): TatamiCSV  {
        // ここで畳の商品情報を取得して、データを詰めてCSVサービスに渡すなど行う
    }
}
```

このActionクラスを、`bootstrap/functions.php`で呼ぶのか、
管理画面で呼ぶのかは特に制限していませんので自由にしてください。

---

## 役割

- 1 Action = 1 ユースケース
- 業務フローの制御を書く
- 実装基板は Service で書く
    - ※`Services`で定義された基盤ロジックを使って、用途に応じた具体的な何かを行う。

例:

- `ImportProductsCsv`
- `ExportOrdersCsv`
- `GenerateInvoicePdf`
- `SendWebhookNotification`
- `SyncExternalApi`

---


# UseCase ディレクトリガイド

`UseCase/` は、**ユースケース単位の処理を組み立てる層**です。
特定のクライアントのユースケースに応じて、使用してください。
※`Support`で定義された基盤ロジックを使って、用途に応じた具体的な何かを行う。

例えば、畳の商品情報をカスタム投稿で管理しており
その Csv 一括取得を行うロジックを書く必要がある場合など。

```php
<?php
class ExportTatamiCsv {
    public function __invoke(): TatamiCsv  {
        // ここで畳の商品情報を取得して、データを詰めてCsvサービスに渡すなど行う
    }
}
```

この Action クラスを、`bootstrap/functions.php`で呼ぶのか、
管理画面で呼ぶのかは特に制限していませんので自由にしてください。

---

## 役割

- 1 Action = 1 ユースケース
- 業務フローの制御を書く
- 実装基板は Service で書く
  - ※`Support`で定義された基盤ロジックを使って、用途に応じた具体的な何かを行う。

例:

- `ImportProductsCsv`
- `ExportOrdersCsv`
- `GenerateInvoicePdf`
- `SendWebhookNotification`
- `SyncExternalApi`

---

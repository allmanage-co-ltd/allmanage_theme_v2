# UseCase ディレクトリガイド

### **※顧客ごとの専用クラスなのでWP依存、使い捨てOKです**

`UseCase/` は、**ユースケース単位の処理を組み立てる層**です。
特定のクライアントのユースケースに応じて、使用してください。

※`Support`や`CMS`の中で定義された基盤ロジックを使う、または使い捨てでゴリっとコードを書いて、用途に応じた具体的な何かを行います。

---

例えば、畳の商品情報をカスタム投稿で管理しており
そのCsvダウンロード行うロジックを書く必要がある場合など。

```php
<?php
class ExportTatamiCsv {
    public function __invoke(): void  {
        // ここで畳の商品情報csvを作成してダウンロードする処理
    }
}
```

この Action クラスを、`bootstrap/functions.php`で呼ぶのか、
管理画面で呼ぶのかは特に制限していませんので自由にしてください。

---

## 例

- `ImportProductsCsv`
- `ExportOrdersCsv`
- `GenerateInvoicePdf`
- `SendWebhookNotification`
- `SyncExternalApi`

---

# Actions ディレクトリガイド

`Actions/` は、**ユースケース単位の処理を組み立てる層**です。

Service / Helper などの基盤ロジックを組み合わせて
「最終的に何を実行するのか」を表現します。

---

## 役割

- 1 Action = 1 ユースケース
- 業務フローの制御を書く
- 処理の流れが読める構造にする
- 実装詳細は Service に委譲する

例:

- `ImportProductsCsv`
- `ExportOrdersCsv`
- `GenerateInvoicePdf`
- `SendWebhookNotification`
- `SyncExternalApi`

---

## 責務

- 入力値を受け取る
- 必要な Service を呼び出す
- フローを制御する
- 例外を呼び出し元へ返す

---

## 非責務

- 直接 DB クエリを書かない
- 直接 cURL を書かない
- 直接 fopen しない
- HTML を描画しない
- WordPress グローバル状態を直接操作しすぎない

それらは Service / Wrapper の責務です。

---

## 書き方の目安

- 名前は「動詞 + 対象」
  - `ImportProductsCsv`
  - `SyncOrders`
  - `GenerateInvoicePdf`
- 依存はコンストラクタ注入
- 返り値型をできるだけ明示
- 例外を握りつぶさない
- 状態を持たない（ステートレス設計）

---

## サンプル

```php
<?php

namespace App\Actions;

use App\Services\CSV;
use App\Repositories\ProductRepository;

class ImportProductsCsv extends Action
{
    // コードはサンプルです
    public function __construct(
        private CSV $csv,
        private ProductRepository $repo,
    ) {}

    // コードはサンプルです
    public function __invoke(): void
    {
        $rows = $this->csv->read();

        foreach ($rows as $row) {
            $this->repo->save($row);
        }
    }
}
```

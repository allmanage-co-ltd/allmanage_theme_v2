# bootstrap ディレクトリガイド

`bootstrap/` はテーマの起動と、テンプレートから使うグローバル関数の入口です。

## ファイルの役割

### `app.php`

- `App` クラスを定義し、テーマ起動時の入口をまとめる
- 今回の更新で、`BootableWpHookInterface` を実装したクラスは `HooksAutoLoader` により **自動 Boot** される
- 方針:
  - **ここには処理ロジックを書かず、起動の配線だけを書く**
  - 個別クラスを毎回手動登録する場所ではなく、オートローダー起動の入口として保つ

### `functions.php`

- テンプレートから利用するグローバル関数を集約する
- 例: `the_view()`, `the_layout()`, `the_component()`, `config()`, `url()` など
- 方針:
  - ここで状態管理や複雑な分岐を持たない
  - 実処理は `app/` 側のクラスへ委譲する
  - 関数コメントに使用例を残す

## 変更時のルール

- 新しい機能を追加する時:
  1. 先に `app/` 側にクラス実装
  2. Hook 起動が必要なら `BootableWpHookInterface` を実装
  3. 必要な場合のみ `bootstrap/functions.php` に薄いラッパーを追加

- `functions.php`（テーマ直下）はエントリーポイントのため、原則触らない

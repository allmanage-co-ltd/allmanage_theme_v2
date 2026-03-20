# WordPress ディレクトリガイド

`app/WordPress/` は WordPress 依存処理をまとめる場所です。
WordPress の都合で複雑になりやすい処理を、この配下に閉じ込めます。

## サブディレクトリ

- `Hooks/`
  - テーマ全体のフック登録
- `Admin/`
  - 管理画面だけに関わる処理
- `Plugins/`
  - プラグイン依存処理
- `Presenter/`
  - 表示補助
- `Wrapper/`
  - WordPress 標準クラスの薄いラッパー
- `Csv/`
  - WordPress 上で動く CSV import / export 一式

## ルール

- WordPress 関数を使う処理はまずここに寄せる
- `boot()` を持つ起動クラスは `BootableWpHookInterface` を実装する
- 画面を止める時は `wp_die()` を直接書かず `AppError::abort()` を使う
- CSV 関連は `Csv/` 配下で完結させる

## 意図

WordPress 依存コードは完全にきれいに分離しきれない場面があります。
その場合でも、どこを見ればよいか分かるようにこのディレクトリへ集約します。

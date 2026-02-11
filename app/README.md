# app ディレクトリガイド

`app/` はこのテーマのアプリケーション層です。

WordPress のフック起点の処理、管理画面拡張、プラグイン連携、共通サービスをここに集約しています。

## ルール

1. `app`の中でのWordpressの関数やグローバル変数の使用は`CMS`の中でのみ許容しています。
2. `Services`や`Helpers`から`CMS`で定義したクラス・メソッドを呼び出してはいけません。（Wordpressに依存してしまうため）
    - 例：`\App\Services\Hoge`から`\App\CMS\Wrapper\MyDpdb`を呼び出してはならない
    - 例：上記の逆（CMS側からServicesを呼ぶ）はOK、むしろ推奨
3.

## 構成

```txt
app/
├─ CMS/                  ※Wordpressへの依存はこの中のみ
│  │
│  ├─ Hooks/             # テーマ全体のフック（描画に関わらないもの）
│  ├─ Admin/             # 管理画面拡張（投稿タイプ・タクソノミー・メニュー等）
│  ├─ Plugins/           # 外部プラグイン連携
│  ├─ Presenter/      # 描画補助（View, Breadcrumb, Pagination 等）
│  └─ Wrapper/           # Wordpress関数を使いやすくラップした実装
│
├─ Services/
│  └─ ...                # 状態、副作用、静的メソッドを許容したロジック
└─ Helpers/              # 軽量ヘルパー（状態、副作用も持たない軽量な実装）
```

## 実装方針

- テーマ直下のテンプレートや `bootstrap/functions.php` に重いロジックを書かない
- 設定値は `config/*.php` に置き、`App\Services\Config::get` から読む
- ログは `App\Services\Logger` を利用する（未定）

## 追加時の目安

- 既存クラスの責務を壊さない（迷ったら新規クラスを切る）
- 依存する対象（WP本体/外部プラグイン/汎用処理）で置き場所を決める

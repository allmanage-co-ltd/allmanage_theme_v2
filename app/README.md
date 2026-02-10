# app ディレクトリガイド

`app/` はこのテーマのアプリケーション層です。
WordPress のフック起点の処理、管理画面拡張、プラグイン連携、共通サービスをここに集約しています。

## 構成

```txt
app/
├─ CMS/
│  ├─ Hooks/      # テーマ全体のフック（setup/enqueue/shortcode/seo 等）
│  ├─ Admin/      # 管理画面拡張（投稿タイプ・タクソノミー・メニュー等）
│  ├─ Plugins/    # 外部プラグイン連携
│  ├─ Views/      # 描画補助（Presenter, Breadcrumb, Pagination 等）
│  ├─ Utils/      # WP補助ユーティリティ
│  └─ Databases/  # DB関連の基底実装
├─ Services/
│  ├─ HTTP/       # HTTPクライアント/入力/セッション
│  ├─ CSV/        # CSV入出力
│  ├─ PDF/        # PDF出力
│  └─ ...         # Config, Logger, Runtime など
└─ Helpers/       # 軽量ヘルパー
```

## 命名と責務の基本ルール

- **Hooks 系**: `boot()` 内で WordPress フックを登録する
- **Admin 系**: 管理画面に関する機能に限定する
- **Plugins 系**: プラグイン固有の連携ロジックを閉じ込める
- **Services 系**: WP 依存を減らした再利用可能な処理を実装する
- **Views 系**: テンプレート描画時に使う振る舞いだけを持つ

## 実装方針

- テーマ直下のテンプレートや `bootstrap/functions.php` に重いロジックを書かない
- まず `app/` 側にクラスを作り、必要ならグローバル関数から呼ぶ
- 設定値は `config/*.php` に置き、`App\Services\Config` から読む
- ログは `App\Services\Logger` を利用する

## 追加時の目安

- 既存クラスの責務を壊さない（迷ったら新規クラスを切る）
- 依存する対象（WP本体/外部プラグイン/汎用処理）で置き場所を決める
- テンプレートの都合で必要な処理でも、可能な限り `Services` または `CMS/Views` へ寄せる

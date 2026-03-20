# allmanage_theme_v2

- **案件に入る前に、この README と `app/README.md` を先に読んでください。**
- **古いドキュメントやコメントが残ることがあるため、迷ったら本 README と各ディレクトリ README の方針を優先してください。**

## このテーマの考え方

このテーマは、WordPress テーマで起きがちな「どこに何を書くか分からない」を減らすために、次の 4 つを明確に分けます。

- `WordPress/` : WordPress 依存コード
- `assets/` : CSS / JS / 画像などのフロント資産
- `Project/` : 案件別コード（外部メンバーが主に触る場所）
- `Interfaces/` : テーマ全体の共通契約

加えて、終了系エラーは `Errors/` にまとめ、`wp_die()` を直接散らさない方針です。

## まず触る場所

- 画面表示を変えたい → `views/` と `assets/`
- WordPress の挙動を変えたい → `app/WordPress/`
- 案件固有ロジックを追加したい → `app/Project/`
- view から使う関数を確認したい → `bootstrap/functions.php`
- 投稿タイプや URL を変えたい → `config/`

## app 配下の実務ルール

- `app/Project/` は外部メンバーが触る唯一の PHP ディレクトリとして扱う
- そのため `Project/` 内は細分化しすぎず、案件単位で見つけやすさを優先する
- WordPress 依存は `app/WordPress/` に寄せる
- 再利用したい処理は `app/Shared/` に寄せる
- 共通ルールは `app/Interfaces/` に置く
- 終了系エラーは `app/Errors/` に寄せる

## ディレクトリ概要

```text
├─ app/
│  ├─ Errors/       # wp_die() 等の終了系エラーをラップする窓口
│  ├─ Interfaces/   # テーマ全体のトップレベル契約
│  ├─ Project/      # 案件別コード（外部メンバーの主作業場所）
│  ├─ Shared/       # 共通ユーティリティ・基盤処理
│  └─ WordPress/    # WordPress 依存コード
├─ assets/          # CSS / JS / 画像 / SCSS
├─ bootstrap/       # 起動処理・グローバル関数
├─ config/          # 設定ファイル
├─ views/           # テンプレート描画
└─ tests/           # テスト
```

## namespace とディレクトリのズレについて

歴史的経緯により、ファイルの置き場所と namespace 名にズレがあります。

- `app/WordPress/` には `App\CMS\...` や `App\Packages\Csv\...` がある
- `app/Shared/` には `App\Support\...` がある
- `app/Project/` には `App\UseCase\...` がある

このズレを吸収するため、Composer の autoload は PSR-4 に加えて `classmap` も使っています。
大規模にリネームせずとも、現行テーマを保守しやすくするための暫定ではなく、現実的な運用ルールです。

## エラー処理方針

- `wp_die()` を直接各所に書かない
- テーマ全体で終了系エラーを差し替えられるよう、`app/Errors/AppError` を経由する
- WP 実行環境では既定で `wp_die()`、将来は CLI / API / テスト向け実装へ差し替え可能

## よく使うコマンド

```bash
composer test
composer run analyse
composer run rector
```

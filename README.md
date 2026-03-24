# allmanage_theme_v2

- **日々更新していきますので、必ず、案件ごとに新しく git clone もしくは zip を落として使用してください。**

- **この README は、全体像や基本ルール、動作環境等を把握するために必ず一読し、各ディレクトリの README も必ず一読してから実装に入ってください。**

- **テーマの変更履歴は`VERSION.md`に記載しています**

- **※コメントは 80%ほど AI に書いてもらっていますので、一部おかしい部分があるかもしれません。違和感のあるコメントはご指摘ください。**

## このテーマの方針

外部のコーダーさんは下記の必須項目のみ把握していただければコーディング可能です。

初めて作業する際は先にドキュメントと必須ディレクトリをざっと確認してください。

| ディレクトリ | 役割 | 必須/任意 |
| --- | --- | --- |
| `config`                  | カスタム投稿の追加やCSSの読み込みなど | **必須** |
| `views`                   | テンプレートページのコーディング      | **必須** |
| `assets`                  | 画像、JS、CSSなどの配置            | **必須** |
| `bootstrap/functions.php` | viewsから呼び出し可能なグローバル関数 | **必須** |
| `app/Project/`            | 案件固有のPHPロジック               | 任意 |
| `app/Plugins/`            | プラグインの挙動を変えたい            | 任意 |
| `app/Hooks/`              | WordPress の挙動を変えたい          | 任意 |

### `views`ディレクトリ内について
```sh
views/
├── app/       # 管理画面のオプションページテンプレート（configでディレクトリ変更可能）
├── archive/   # archive-○○.php に対応
├── component/ # views内で共通使用するコンポーネント（the_component()で呼び出し可能）
├── layout/    # headerやfooterなどの共通レイアウト（header/footerは各テンプレートで自動読み込み）
├── page/      # page-○○.php に対応。ファイル名＝パーマリンク、フォルダ階層＝URL階層（例: page/contact/thanks → /contact/thanks）
├── single/    # single-○○.php に対応
└── taxonomy/  # taxonomy-○○.php に対応
```

## テーマドキュメント一覧

- [テーマについて（本ドキュメント）](https://github.com/allmanage-co-ltd/allmanage_theme_v2/blob/master/README.md)
- [テーマ変更履歴](https://github.com/allmanage-co-ltd/allmanage_theme_v2/blob/master/VERSION.md)
- [VIEWS の記載について](https://github.com/allmanage-co-ltd/allmanage_theme_v2/blob/master/views/README.md)
- [SASS の記載について](https://github.com/allmanage-co-ltd/allmanage_theme_v2/blob/master/assets/scss/README.md)
- [app ディレクトリについて](https://github.com/allmanage-co-ltd/allmanage_theme_v2/blob/master/app/README.md)
- [bootstrap ディレクトリについて](https://github.com/allmanage-co-ltd/allmanage_theme_v2/blob/master/bootstrap/README.md)

## 対応バージョン

- PHP：8.0+

- Wordpress：6.0+

## Sass について

基本は vscode プラグインの Live Sass Compailer を使用します。

または`Makefile`に定義したショートカットコマンドを利用して npx でコンパイルします。

どちらの方法でコンパイルしても構いません。

```sh
make dev type=style    # → style.scss をコンパイル監視
make dev type=include  # → include.scss をコンパイル監視
```

## 推奨プラグインについて

- `MW WP Form`

  - お問い合わせフォーム作成

- `WPvivid Backup Plugin`

  - バックアップ・データ移行

- `Advanced Custom Fields Pro`

  - 各種カスタムフィールド・オプションページ
  - `\\IODATA-35a52a\disk1\【顧客情報】\■Allmanage自社関連情報\●各種サービス・システム関係\Advanced Custom Fields Pro（ACF）`

- `All In One SEO`

  - 主に AIO 対策を意識した SEO 対策として導入

- `XML Sitemap Generator for Google`

  - サイトマップ生成（`All In One SEO`有効時は不要）

- `Website LLMs.txt`

  - AIO 対策のため導入（`All In One SEO`有効時は不要）

## Composer について

Composer 環境でで構成されていますので、本番、テストに関わらず、テーマを動かすには`Composer`環境および`composer install`でインストールされる依存ライブラリ`vendor`の配置が**必須**です。

テーマ直下に`vendor`がなければテーマが動きませんが、git 管理の許容範囲かつ、着手ハードルを下げるため、`vendor`も git 管理に含めており、クローンもしくは zip で展開した段階から動くようになっています。

> #### `Composer`とは
>
> 引用は関係ない参考記事：https://qiita.com/akira-hagi/items/553da1e122f7c300d6ac

> Composer とは、特定のコマンドを打つだけで PHP のライブラリがインストールできる PHP のパッケージ管理ツールです。
> 有名な HTTP クライアントの`Guzzle`や、メール送信ライブラリの`PHPMailer`を簡単に導入できます。

> その他にも、ショートカットコマンドの登録や、PHP クラスのオートロード、ファイルのグローバル読み込みなど便利に使えます。

`Composer`を使用してみたい場合は下記を参考にインストールしてください。
[https://kinsta.com/jp/blog/install-composer/](https://kinsta.com/jp/blog/install-composer/)

```sh
# Composerインストール後にテーマ直下のディレクトリで
# シェルで書きコマンドを叩き依存関係をインストールします。
# 問題なくインストールできればテーマが稼働します。
composer install
```

### よく使う Composer スクリプト

```bash
# 静的解析、エラー個所やエラーになりうる危険なコードをチェックできる
composer run analyse      # PHPStan

# PHPバージョンアップ時のソースコード自動修正
composer run rector       # Rector (実行せず結果を確認)

# PHPバージョンアップ時のソースコード自動修正
composer run rector-fix   # Rector （実行）

# Pestで書いたテストファイルを実行する
composer test
```

## Docker について

お使いの PC に Docker 及び Docker Desktop がインストール済みの場合、Local 等で開発環境をセットせずに 1 コマンドで Wordpress のセットアップが可能です。

Wordpress => [http://localhost:8888](http://localhost:8888)

PhpMyAdmin => [http://localhost:8889](http://localhost:8889)

```sh
# 開発環境の起動
docker compose up

# 開発環境の中止
docker compose stop

```

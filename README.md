# allmanage_theme_v2

- **日々更新していきますので、必ず、案件ごとに新しく git clone もしくは zip を落として使用してください。**

- **この README は、全体像や基本ルール、動作環境等を把握するために必ず一読し、各ディレクトリの README も必ず一読してから実装に入ってください。**

- **テーマの変更履歴は`VERSION.md`に記載しています**

- **※コメントは 80%ほど AI に書いてもらっていますので、一部おかしい部分があるかもしれません。違和感のあるコメントはご指摘ください。**

## テーマドキュメント一覧

- [テーマについて（本ドキュメント）](https://github.com/allmanage-co-ltd/allmanage_theme_v2/blob/master/README.md)
- [テーマ変更履歴](https://github.com/allmanage-co-ltd/allmanage_theme_v2/blob/master/VERSION.md)
- [VIEWS の記載について](https://github.com/allmanage-co-ltd/allmanage_theme_v2/blob/master/views/README.md)
- [SASS の記載について](https://github.com/allmanage-co-ltd/allmanage_theme_v2/blob/master/assets/scss/README.md)
- [app ディレクトリについて](https://github.com/allmanage-co-ltd/allmanage_theme_v2/blob/master/app/README.md)
- [bootstrap ディレクトリについて](https://github.com/allmanage-co-ltd/allmanage_theme_v2/blob/master/bootstrap/README.md)

## このテーマの整理方針

外部のコーダーさんはテーマを理解する必要がないように作っています。

保守を担当する社内のエンジニアはある程度理解しておくとカスタマイズやちょっとした更新作業がスムーズに行えますがドキュメントを整備しているので都度でも大丈夫です。

1. 基本的には`views` `config` `assets` `bootstrap/functions.php` だけを見ればコーディング作業ができるように作っていますので、これらのディレクトリをチェックいただければ大丈夫です。

2. WordPress 標準ではテーマ直下にファイルが散らばりますが、それらの表示コードはすべて`views`ディレクトリや`bootstrap/functions.php`に逃がしていますので、テーマ直下を触る必要はありません。

3. CSS や JS の登録、カスタム投稿・タクソノミーの作成、ページ URL の設定は`config`ディレクトリを編集してください。その設定ファイルをもとに`app`ディレクトリの中でロジックを組んでいます。

4. `views`から呼び出す関数は全て`bootstrap/functions.php`にまとめています。このファイルはグローバルに呼び出すことを許容しており、詳細なロジックは`app`に逃がしています。ここにある関数が`views`から使える関数の全てとなります。

- カスタム投稿や CSS を追加したい → `config/`
- 画像や JS、CSS を配置したい → `assets/`
- ページを作成したい → `views/`
- view から使う関数を確認したい → `bootstrap/functions.php`

- WordPress の挙動を変えたい（任意） → `app/Hooks/`
- プラグイン連携を変えたい（任意） → `app/Plugins/`
- 案件別ロジックを追加したい（任意） → `app/Project/`

## 対応バージョン

- PHP：8.0+

- Wordpress：6.0+

## Sass について

基本は vscode プラグインの Live Sass Compailer または、
`Makefile`に定義したショートカットコマンドを利用して npx でコンパイルします。

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

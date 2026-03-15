# allmanage_theme_v2

- **日々更新していきますので、必ず、案件ごとに新しく git clone もしくは zip を落として使用してください。**

- **この README は、全体像や基本ルール、動作環境等を把握するために必ず一読し、各ディレクトリの README も必ず一読してから実装に入ってください。**

- **テーマの変更履歴は`VERSION.md`に記載しています**

- **※コメントは 80%ほど AI に書いてもらっていますので、一部おかしい部分があるかもしれません。違和感のあるコメントはご指摘ください。**

## テーマドキュメント一覧

- [テーマについて（本ドキュメント）](https://github.com/allmanage-co-ltd/allmanage_theme_v2/blob/master/README.md)
- [テーマ変更履歴](https://github.com/allmanage-co-ltd/allmanage_theme_v2/blob/master/VERSION.md)
- [VIEWSの記載について](https://github.com/allmanage-co-ltd/allmanage_theme_v2/blob/master/views/README.md)
- [SASSの記載について](https://github.com/allmanage-co-ltd/allmanage_theme_v2/blob/master/assets/scss/README.md)
- [appディレクトリについて](https://github.com/allmanage-co-ltd/allmanage_theme_v2/blob/master/app/README.md)
- [CMSディレクトリについて](https://github.com/allmanage-co-ltd/allmanage_theme_v2/blob/master/app/CMS/README.md)
- [Supportディレクトリについて](https://github.com/allmanage-co-ltd/allmanage_theme_v2/blob/master/app/Support/README.md)
- [UseCaseディレクトリについて](https://github.com/allmanage-co-ltd/allmanage_theme_v2/blob/master/app/UseCase/README.md)

## テーマの方針

- ドキュメントの整備と保守性の高いソースコードで、数年後に触るエンジニアが楽に修正できるようにする
- 見る必要のないコードを見なくてもコーディングできるようにして、スピード＆クオリティを高める

## 運用ルール

1. クラスや関数には必ずコメントを残すようにする。最低でも一言何をしているか。余裕があれば詳細に残す。チームで取り組む以上は後から他のエンジニアがそのコードを必ず読むため。コメントも資産になります。

2. ロジックを書く際は、まず今後も使用できるかを考える、今後も使用できるなら`Support`に基盤を残す、案件ごとに使い捨てではなく資産化できるソースコードは積極的にプルリクする。

3. `app`ディレクトリを除き、不用意にディレクトリを切ることは推奨しません。どうしてもな場合は弊社エンジニアに相談し、テーマに取り入れてもらってください。

4. デフォルト状態のテーマで、誤りや改善点を見つけた場合は必ず弊社エンジニアに報告してください。

5. 弊社のコーディング作業は委託が多いため、厳格なコーディングルールは設けていませんが、独りよがりであきらかに煩雑なコードは、後から触るエンジニアの時間を奪う行為になるため、見つけ次第で指摘させていただきます。

## コーディングについて

1. 基本的には`views` `config` `assets` `bootstrap/functions.php` だけを見ればコーディング作業ができるように作っていますので、一番最初はこれらのディレクトリをチェックしてください。

2. WordPress 標準ではテーマ直下にファイルが散らばりますが、それらの表示コードはすべて`views`ディレクトリに逃がしていますので、テーマ直下を触る必要はありません。

3. CSS や JS の登録、カスタム投稿・タクソノミーの作成、ページ URL の設定は`config`ディレクトリを編集してください。その設定ファイルをもとに`app`ディレクトリの中でロジックを組んでいます。

4. `views`から呼び出す関数は全て`bootstrap/functions.php`にまとめています。このファイルはグローバルに呼び出すことを許容しており、詳細なロジックは`app`に逃がしています。

5. アドバイスや改善点は積極的に提案していただき、この先関わるエンジニアが生産性高く作業できるように、より良いテーマにしたいです。

---

## ディレクトリ概要

```
├─ app/                # PHP実装（CMS連携・基盤ロジック・ユースケース）
├─ assets/             # CSS / JS / 画像 / SCSS
├─ bootstrap/          # CMS起動処理・グローバル関数
├─ config/             # 各種設定（投稿タイプ、タクソノミー、パーマリンク、管理画面メニューなど）
├─ views/              # page/single/archive/taxonomy/layout/componentの描画
├─ tests/              # ロジックのテスト用（Pest採用）
├─ functions.php       # テーマ起動エントリー（原則編集しない）
└─ docker-compose.yaml # ローカル開発用WordPress環境
```

## 開発時に主に触る場所

- 画面表示を変更したい: `views/` と `assets/`
- WP の挙動やフックを変更したい: `app/CMS/`
- カスタム投稿タイプ・タクソノミー: `config/cms.php`
- プラグイン特有のフックやカスタム: `app/CMS/Plugins/`
- 使いまわせる基盤ロジック/汎用ユーティリティ: `app/Support/`
- 顧客毎のユースケースロジック・処理: `app/UseCase/`
- view テンプレートから使う関数: `bootstrap/functions.php`
  - 処理本体は `app/` の定説なクラスへ実装してください
  - テーマ直下の`functions.php` は編集しません

## 対応バージョン

- PHP：8.0+

- Wordpress：6.0+

## Sass について

基本は vscode プラグインの Live Sass Compailer を使用します。

コンパイルのルール（入出力先）などは`./.vscode/settings.json`に記載してあるのでそのままコンパイルしていただければ問題ありません。

## 推奨プラグインについて

- `WPvivid Backup Plugin`

  - バックアップ・データ移行

- `Advanced Custom Fields Pro`

  - 各種カスタムフィールド・オプションページ
  - `\\IODATA-35a52a\disk1\【顧客情報】\■Allmanage自社関連情報\●各種サービス・システム関係\Advanced Custom Fields Pro（ACF）`

- `XML Sitemap Generator for Google`

  - サイトマップ生成

- `Website LLMs.txt`

  - AIO 対策のため導入

- `MW WP Form`
  - お問い合わせフォーム作成

## Composerについて

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

## Dockerについて

お使いの PC に Docker 及び Docker Desktop がインストール済みの場合、Local 等で開発環境をセットせずに 1 コマンドで Wordpress のセットアップが可能です。

Wordpress => [http://localhost:8888](http://localhost:8888)

PhpMyAdmin => [http://localhost:8889](http://localhost:8889)

```sh
# 開発環境の起動
docker compose up

# 開発環境の中止
docker compose stop

```

### 今後の展望

- Trait, Interface, Enum 等の言語機能も活用していきたいが Wordpress では使わないか。。

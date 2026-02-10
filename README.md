# Allmanageテーマ

従来の既存テーマの古い記述や、グローバル関数をひっかきまわす構成を一新して、新たにオブジェクト指向の「クラス」と型定義を取り入れソースが肥えてきたとしてもある程度の管理コストで理解できる塩梅を目指しています。

**作成中**

## 方針

- 何がどこにあるかわからない煩雑なテーマを辞めて、見るべきディレクトリを明確化することでスピード＆クオリティを高める
- グローバルに呼べるbootstrap/functions.phpにはロジックを書かずに実装を他へ逃がす事で必要のない実装を見なくて良くする
- ロジックにはクラスを取り入れることで新機能の追加によるテーマ肥大化コストを減らしテーマを資産として蓄積する
- ロジックには型定義を積極的に書くことで何が返るか明確にして不安要素をを減らす
- 古い書き方を辞めてモダンな書き方に触れることで技術者がLaravelにもスイッチしやすくする

## コーディングについて
### **日々更新していきますので、必ず、案件ごとに新しく git clone もしくは zip を落として使用してください。**

1. 基本的には`views` `config` `assets` `bootstrap/functions.php` だけを見ればコーディング作業ができるように作っていますので、一番最初はこれらのディレクトリをチェックしてください。

2. WordPress標準ではテーマ直下にファイルが散らばりますが、それらをすべて`views`ディレクトリに逃がしていますので、テーマ直下を触る必要はありません。

3. CSSやJSの登録、カスタム投稿・タクソノミーの作成、ページURLの設定は`config`ディレクトリを編集してください。その設定ファイルをもとに`app`ディレクトリの中でロジックを組んでいます。

4. `views`から呼び出す関数は全て`bootstrap/functions.php`にまとめています。このファイルはグローバルに呼び出すことを許容しており、詳細なロジックは`app/Services`に逃がしています。

2. アドバイスや改善点は積極的に提案し、より良いテーマにしたいです。

## 動作環境について

モダンPHPで構成されていますので、本番、テストに関わらず、テーマを動かすには`Conposer`環境が**必須**です。
未インストールの場合は下記を参考にインストールしてください。
[https://kinsta.com/jp/blog/install-composer/](https://kinsta.com/jp/blog/install-composer/)

```sh
# conposerインストール後にテーマ直下のディレクトリで
# シェルで書きコマンドを叩き依存関係をインストールします。
# 問題なくインストールできればテーマが稼働します。
cd allmanage_theme
composer install
```

### Dockerでの開発環境

お使いのPCにDocker及びDocker Desktopがインストール済みの場合、Local等で開発環境をセットせずに1コマンドでWordpressのセットアップが可能です。

```sh
# 開発環境の起動
docker compose up

# 開発環境の中止
docker compose stop

```
Wordpress  => [http://localhost:8888](http://localhost:8888)

PhpMyAdmin => [http://localhost:8889](http://localhost:8889)


## Sassについて

基本は vscode プラグインのLive Sass Compailerを使用します。

コンパイルのルール（入出力先）などは`./.vscode/settings.json`に記載してあるのでそのままコンパイルしていただければ問題ありません。

## 必須プラグインについて
1. `WPvivid Backup Plugin` バックアップ・データ移行

2. `Advanced Custom Fields Pro` 各種カスタムフィールド・オプションページ `\\IODATA-35a52a\disk1\【顧客情報】\■Allmanage自社関連情報\●各種サービス・システム関係\Advanced Custom Fields Pro（ACF）`

3. `XML Sitemap Generator for Google` サイトマップ生成

4. `Website LLMs.txt` AIO 対策のため導入

5. `mw wp form` お問い合わせフォーム作成

---

## View構成

```
views/
├─ page/
├─ single/
├─ archive/
├─ taxonomy/
├─ component/
└─ layout/
└─ admin/
```

- テンプレート階層は App\Services\View\Render で解決
- WordPress標準テンプレート階層（テーマ直下）は一切使わない

---

## アプリケーション構成

```
app/
├─ Hooks/             WPコアフック
├─ Hooks/Admin/       管理画面拡張フック
├─ Hooks/Plugins/     プラグイン連携フック
├─ Services/          共通サービス、特定の汎用機能、viewから逃がしたいロジック等
└─ Views/             ページの表示切り替え、コンポーネント描画、状態を持つモジュール用ロジック
```
---

## 起動

テーマ読み込み時に `bootstrap/App` が起動され
各 Hooks クラスの `boot()` が呼ばれることで初期化される

```
functions.php
 └─ bootstrap/App::boot()
```

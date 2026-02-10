# allmanage_theme_v2

Allmanage の WordPress テーマを、**責務分離しやすい構成**に再編した最新版です。
従来の「テーマ直下にロジックが散らばる」状態を避け、`app`（PHPロジック）・`config`（設定）・`views`（表示）・`assets`（フロント）に役割を整理しています。

---

## このテーマの考え方

- テーマ直下は WordPress のエントリーポイントに限定し、実処理は `app/` 配下へ寄せる
- グローバル関数は `bootstrap/functions.php` のみに集約し、重いロジックはクラスへ移譲する
- 設定値は `config/*.php` に寄せ、`config('...')` で参照する
- 表示テンプレートは `views/` に統一し、`Presenter` 経由で描画する
- PHP 8.2 前提 + Composer オートロードを標準とする

---

## ディレクトリ概要

```txt
.
├─ app/                # テーマのPHP実装（CMS連携・サービス・ヘルパー）
├─ assets/             # CSS / JS / 画像 / SCSS
├─ bootstrap/          # 起動処理・グローバル関数
├─ config/             # 各種設定（投稿タイプ、タクソノミー、URLなど）
├─ views/              # page/single/archive/taxonomy/layout/component/admin/pdf
├─ functions.php       # テーマ起動エントリー（原則編集しない）
└─ docker-compose.yaml # ローカル開発用WordPress環境
```

---

## 起動フロー

1. `functions.php` が Composer の `vendor/autoload.php` を読み込む
2. `bootstrap/app.php` の `App` クラスを読み込む
3. `(new App())->boot()` で、Hook/Admin/Plugin クラス群を起動する
4. Composer の `autoload.files` で `bootstrap/functions.php` のグローバル関数が利用可能になる

---

## 開発時に主に触る場所

- 画面表示を変更したい: `views/` と必要に応じて `assets/`
- WP の挙動やフックを変更したい: `app/CMS/Hooks/`
- 管理画面・投稿タイプ・タクソノミー: `app/CMS/Admin/` と `config/`
- ACF / MW WP Form / Welcart 連携: `app/CMS/Plugins/`
- 汎用処理（設定、ログ、CSV/PDF、HTTP）: `app/Services/`
- テンプレートから使う関数を追加: `bootstrap/functions.php`（実処理は app 側へ）

---

## セットアップ

### 1) Composer 依存のインストール

```bash
composer install
```

### 2) Docker で WordPress を起動（任意）

```bash
docker compose up -d
```

- WordPress: <http://localhost:8888>
- phpMyAdmin: <http://localhost:8889>

停止:

```bash
docker compose stop
```

---

## よく使う Composer スクリプト

```bash
composer run cs       # PHP-CS-Fixer (dry-run)
composer run cs:fix   # PHP-CS-Fixer 実行
composer run analyse  # PHPStan
composer run rector   # Rector (dry-run)
composer run rector:fix
```

---

## 補足

- `functions.php` は最小責務のため、基本的に編集しません
- テーマのグローバル関数は `bootstrap/functions.php` へ追加します
- ただしロジック肥大化を避けるため、処理本体は `app/` のクラスへ実装してください

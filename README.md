# allmanage_theme_v2

- 案件着手前にこの README と `app/README.md` を読んでください。
- 古いコメントや古いドキュメントが残っている場合は、最新の README と実装構成を優先してください。

## このテーマの整理方針

このテーマでは、外部メンバーでも迷いにくいように次を分けています。

- `app/WordPress/` : WordPress 依存コード
- `assets/` : CSS / JS / 画像などのフロント資産
- `app/Project/` : 案件別コード
- `app/Shared/` : 再利用したい共通資産
- `app/Interfaces/` : 最小限の共通ルール
- `app/Errors/` : 終了系エラーの共通窓口

## まず触る場所

- 画面表示を変えたい → `views/` と `assets/`
- WordPress の挙動を変えたい → `app/WordPress/`
- 案件別ロジックを追加したい → `app/Project/`
- view から使う関数を確認したい → `bootstrap/functions.php`
- 設定を変えたい → `config/`

## app 配下の実務ルール

- `Project/` は外部メンバーが主に触る唯一の PHP ディレクトリとして扱う
- そのため `Project/` 内は細分化しすぎない
- WordPress 依存は `WordPress/` に寄せる
- 再利用したい処理は `Shared/` に寄せる
- 処理停止は `AppError::abort()` に寄せる

## ディレクトリ概要

```text
├─ app/
│  ├─ Errors/
│  ├─ Interfaces/
│  ├─ Project/
│  ├─ Shared/
│  └─ WordPress/
├─ assets/
├─ bootstrap/
├─ config/
├─ views/
└─ tests/
```

## namespace 方針

- `app/WordPress/` は `App\WordPress\...`
- `app/Project/` は `App\UseCase\...`
- `app/Shared/` は `App\Support\...`

少なくとも `WordPress/` 配下は、ディレクトリと namespace が素直に対応する状態を維持します。

## エラー処理方針

- `wp_die()` を直接散らさない
- テーマ内で処理を止める時は `AppError::abort()` を使う
- 終了方法を変えたくなった時は `app/Errors/AppError.php` だけを見ればよい状態にする

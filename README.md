# allmanage_theme_v2

- 案件着手前にこの README と `app/README.md` を読んでください。
- 古いコメントや古いドキュメントが残っている場合は、最新の README と実装構成を優先してください。

## このテーマの整理方針

このテーマでは、外部メンバーでも迷いにくいように次を分けています。

- `app/Hooks/` : WordPress 起動処理
- `app/Plugins/` : プラグイン依存処理
- `app/Services/` : 共通サービス
- `app/Helpers/` : 小さい再利用関数
- `app/Presenters/` : View 組み立て
- `app/Project/` : 案件固有コード
- `app/Interfaces/` / `app/Enums/` : 契約と定義
- `app/Error/` : 終了系エラーの共通窓口

## まず触る場所

- 画面表示を変えたい → `views/` と `assets/`
- WordPress の挙動を変えたい → `app/Hooks/`
- プラグイン連携を変えたい → `app/Plugins/`
- 案件別ロジックを追加したい → `app/Project/`
- view から使う関数を確認したい → `bootstrap/functions.php`
- 設定を変えたい → `config/`

## app 配下の実務ルール

- `Project/` は案件固有コードの一時置き場として使ってよい
- 資産化できそうになったら `Hooks/`, `Services/`, `Helpers/` へ切り出す
- Hook 起動クラスは `BootableWpHookInterface` 実装を基本とする
- 処理停止は `AppError::abort()` に寄せる
- 標準関数の繰り返しが増えたら `Helpers/` を育てる

## ディレクトリ概要

```text
├─ app/
│  ├─ Enums/
│  ├─ Error/
│  ├─ Helpers/
│  ├─ Hooks/
│  │  └─ Core/
│  ├─ Interfaces/
│  ├─ Plugins/
│  ├─ Presenters/
│  ├─ Project/
│  └─ Services/
├─ assets/
├─ bootstrap/
├─ config/
├─ views/
└─ tests/
```

## namespace 方針

- `app/` 配下は **ディレクトリと namespace を合わせる**
- 例:
  - `app/Hooks/SetupTheme.php` → `App\Hooks\SetupTheme`
  - `app/Services/Http/Session.php` → `App\Services\Http\Session`
  - `app/Presenters/View.php` → `App\Presenters\View`
  - `app/Helpers/Path.php` → `App\Helpers\Path`

## 起動方針

- `bootstrap/app.php` は `HooksAutoLoader` を起動するだけに保つ
- `BootableWpHookInterface` を実装したクラスは自動 Boot 対象になる
- 手動配線が必要なのは特殊ケースのみに寄せる

## エラー処理方針

- `wp_die()` を直接散らさない
- テーマ内で処理を止める時は `AppError::abort()` を使う
- 終了方法を変えたくなった時は `app/Error/AppError.php` だけを見ればよい状態にする

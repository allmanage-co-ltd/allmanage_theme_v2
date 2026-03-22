# app ディレクトリガイド

`app/` はテーマの PHP 実装を置く場所です。
今回の整理後は **ディレクトリ名と namespace / use が素直に対応する** ことを優先しています。

## 方針

- `app/` 配下は **ディレクトリ構造 = namespace** を基本にする
- WordPress の起動対象は `BootableWpHookInterface` を実装して自動 Boot 対象に寄せる
- 案件固有のものはまず `Project/` に置き、あとで資産化できそうなら切り出す
- 標準関数の寄せ先として `Helpers/` を育てていく

---

## ディレクトリごとの役割

### `Enums/`
- 定数のまとまりを型として扱う場所
- 文字列の打ち間違いを減らしたい時に使う
- 例: CSVの列種別、ログフィールド種別

### `Error/`
- テーマ全体で共通に使う終了系エラーの窓口
- `AppError::abort()` から停止・ログ出力・ユーザー向け文言を一元化する

### `Helpers/`
- 小さく再利用しやすいユーティリティを置く
- `Arr`, `Fmt`, `Html`, `Path` など、標準関数の寄せ先にする
- `trim` / `explode` / `dot access` / `path join` のような散りやすい処理はここに逃がす

### `Hooks/`
- WordPress のアクション / フィルターにぶら下がる起動クラスを置く
- テーマ初期化、管理画面調整、投稿タイプ登録、CSV導線などをまとめる
- `Hooks/Core/HooksAutoLoader.php` が自動起動の中心

### `Interfaces/`
- テーマ全体の最小限の契約を置く
- 「何を実装すればこの仕組みに乗るか」を示すためのもの

### `Plugins/`
- ACF や Welcart など、外部プラグイン依存の処理を置く
- プラグインが有効な時だけ動かしたい処理はここに寄せる

### `Presenters/`
- View 寄りの組み立てや出力補助を置く
- パンくず、メタ情報、ページ解決、ページネーションなど

### `Project/`
- **案件固有のものを入れる場所**
- 特に縛りはあえてしない
- 使い捨ての殴り書きでも、まずはここに置いてよい
- 後から「これは他案件でも使えそう」と判断できた時点で、`Hooks/`, `Services/`, `Helpers/` など適切な場所へ切り分ける

### `Services/`
- やや大きめの共通処理や外部I/Oを置く
- `Config`, `Http`, `Csv`, `Logger`, `Query` などのテーマ基盤を集約する

---

## Enum / Interface とは

### Enum
- 「取りうる値の集合」を型として表すもの
- 例: `LogFieldEnum::RequestId`, `CsvColumnActionEnum::SetTerms`
- 文字列ベタ書きより安全で、補完も効きやすい

### Interface
- 「このメソッドを持つこと」という契約
- 実装の中身ではなく、クラスが従うルールを決める
- 例: `BootableWpHookInterface` は `boot(): void` を持つことを保証する

---

## Hook と `BootableWpHookInterface`

### 何をするものか
- WordPress 起動時に読み込ませたいクラスは、基本的に `BootableWpHookInterface` を実装する
- 実装すると `boot(): void` を持つクラスとして扱われる

### 継承 / 実装するとどうなるか
- `bootstrap/app.php` から `HooksAutoLoader` が起動する
- `HooksAutoLoader` は `app/` 配下を走査する
- `BootableWpHookInterface` を実装しているクラスだけを抽出する
- 抽象クラスを除外した上で `new` して `boot()` を自動実行する

つまり、**Hook クラスを手動で1件ずつ登録しなくても、Interface を実装すれば自動 Boot 対象になる** という運用です。

### 使い方の目安
- `add_action()` / `add_filter()` を持つ起動クラス
- 管理画面メニュー登録クラス
- プラグイン連携の初期化クラス
- 投稿一覧カラム編集のような WordPress 依存の起動処理

---

## 迷った時の置き場所

- WordPress 関数に強く依存する → `Hooks/` or `Plugins/`
- 案件専用でまず形にしたい → `Project/`
- 小さい共通関数として何度も出る → `Helpers/`
- ログ / CSV / HTTP / 設定のような基盤 → `Services/`
- View 表示の組み立て → `Presenters/`
- 契約の定義 → `Interfaces/`
- 定数群の整理 → `Enums/`

---

## namespace の目安

- `app/Hooks/SetupTheme.php` → `App\Hooks\SetupTheme`
- `app/Presenters/View.php` → `App\Presenters\View`
- `app/Services/Http/Session.php` → `App\Services\Http\Session`
- `app/Helpers/Arr.php` → `App\Helpers\Arr`
- `app/Project/ExportNewsCsv.php` → `App\Project\ExportNewsCsv`

この対応が崩れた時は、まず namespace / use を見直してください。

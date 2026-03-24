# app ディレクトリガイド

`app/` はテーマの PHP 実装を置く場所です。

## 方針

- WordPress の起動対象は `BootableWpHookInterface` を実装して自動 Boot 対象に寄せる
- 案件固有のものはまず `Project/` に置き、あとで資産化できそうなら切り出す

### エラー処理方針

- テーマ内で処理を止める時は `AppError::abort()` を使う
- 終了方法を変えたくなった時は `app/Error/AppError.php` だけを見ればよい状態にする

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

## ディレクトリごとの役割

### `Enums/`

- 定数のまとまりを型として扱う場所
- 文字列の打ち間違いを減らしたい時に使う
- 例: CSV の列種別、ログフィールド種別

### `Error/`

- テーマ全体で共通に使う終了系エラーの窓口
- `AppError::abort()` から停止・ログ出力・ユーザー向け文言を一元化する

### `Helpers/`

- 小さく再利用しやすいユーティリティを置く

### `Hooks/`

- WordPress のアクション / フィルターにぶら下がる起動クラスを置く
- テーマ初期化、管理画面調整、投稿タイプ登録、CSV 導線などをまとめる
- `Hooks/Core/HooksAutoLoader.php` が自動起動の中心
- `BootableWpHookInterface` を実装することで自動で有効化される

### `Interfaces/`

- テーマ全体の最小限の契約や、強制したいルールを置く
- 「何を実装すればこの仕組みに乗るか」を示すためのもの

### `Plugins/`

- ACF や Welcart など、外部プラグイン依存の処理を置く
- プラグインが有効な時だけ動かしたい処理はここに寄せる

### `Presenters/`

- View 寄りの組み立てや出力補助を置く
- パンくず、メタ情報、ページ解決、ページネーションなど

### `Project/`

- **案件固有のものを入れる場所**
- 特に縛りなしの自由なディレクトリ
- 使い捨ての殴り書きでも、まずはここに置いてよい
- 後から「これは他案件でも使えそう」と判断できた時点で、`Hooks/`, `Services/`, `Helpers/` など適切な場所へ切り分ける

### `Services/`

- やや大きめの共通処理や外部 I/O や 汎用性の高いロジック や ラッパーなどを置く

---

## Enum / Interface とは

### Enum

- 「取りうる値の集合」を型として表すもの
- 例: `LogFieldEnum::RequestId`, `CsvColumnActionEnum::SetTerms`
- 文字列ベタ書きより安全で、補完も効きやすい

### Interface

- 「このメソッドを持つこと」という契約
- 実装の中身ではなく、クラスが従うルールを決める
- 例: `BootableWpHookInterface` は `boot(): void` を持つことを保証することで、どのクラスが何を書いているか知らなくても `boot()` できる

---

## Hook と `BootableWpHookInterface`

### 何をするものか

- WordPress 起動時に読み込ませたいクラスは、基本的に `BootableWpHookInterface` を実装する
- 実装すると `boot(): void` を持つクラスとして扱われる

### 継承 / 実装するとどうなるか

- `bootstrap/app.php` から `HooksAutoLoader` が起動する
- `HooksAutoLoader` は `app/` 配下を走査する
- `BootableWpHookInterface` を実装しているクラスだけを抽出する
- 抽象クラスを除外した上で `boot()` を自動実行する

つまり、**Hook クラスを手動で 1 件ずつ登録しなくても、Interface を実装すれば自動 Boot 対象になる** という運用です。

---

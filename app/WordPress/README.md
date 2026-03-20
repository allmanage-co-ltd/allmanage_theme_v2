# WordPress ディレクトリガイド

`app/WordPress/` は、**WordPress 依存を閉じ込めるためのディレクトリ**です。
フック・管理画面・プラグイン連携・WP ラッパー・CSV パッケージをここに集めます。

## サブディレクトリの役割

- `Hooks/`
  - テーマ全体のフック登録
  - enqueue / shortcode / setup / SEO / package boot など
- `Admin/`
  - 管理画面メニュー、投稿タイプ、タクソノミー、オプションページ
- `Plugins/`
  - ACF / MW WP Form / Welcart などプラグイン依存処理
- `Presenter/`
  - View 解決、Breadcrumb、Pagination など表示補助
- `Wrapper/`
  - `WP_Query` や `$wpdb` まわりの薄いラッパー
- `Csv/`
  - WordPress 上で動く CSV import / export パッケージ本体

## ルール

- WordPress 関数はまずこの配下に寄せる
- `boot()` を持つ起動クラスは `app/Interfaces` のインターフェース契約に合わせる
- 失敗時に処理を止めたい場合も `wp_die()` を直接書かず、`app/Errors` を経由する
- 案件固有の最終調整は `Project/` から呼び出す形を優先する

## 補足

namespace は歴史的に `App\CMS\...` や `App\Packages\Csv\...` を使っています。
そのため、**WordPress ディレクトリ = namespace も WordPress** ではありません。
ファイルの置き場所を基準に責務を判断してください。

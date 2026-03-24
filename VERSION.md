# バージョンアップ履歴

## 2026.3.24

### `app`ディレクトリ内の構造を大幅変更しました。（社内向け）

※外部コーダーさんは基本触らない場所なのでスルーでOKです。

※app内は大幅変更しましたが、viewsやbootstrap/functions.phpの互換性は保っています。

前バージョンまではWP依存を分離してWPに限らずPHP資産を蓄積しようと思っていましたが、
WPテーマなのになにしてんだ、、となったので諦めて保守性に振り切りました。
Laravel使うならLaraevelのレールでコード書くので、あえて流用することはないかなと。。

### `config`に新たに設定可能項目を追加しました。

- `acf`: ACFのフィールド登録用
- `cms.taxonomy_required`: タクソノミーの必須化設定
- `logger.app`,`logger.access`,`logger.error`: ログ関連の設定
- `searchform.filter`: WordPressのデフォルトs検索に、タクソノミー、カスタムフィールドを含める設定

コメントも併せて整備し、どこのファイルで設定値を参照しているか追記しました。

### `views`内を一部整理しました。

NEWSアーカイブの.c-card_newsをコンポーネントに移行。

以下で共通使用。

- `views/archive/news.php`
- `views/taxonomy/news_cat.php`
- `views/page/search.php`

```html
<!-- views/archive/news.php -->
<?php
the_component('news/c-card_news', [
    'query' => $query
]);
?>
```

### `get_acf_action`を`get_acf_fields`に変更、仕様変更に伴うシグネチャの変更

```php

// 前バージョン
// $sample = get_acf_action( get_the_ID() )->news();
// echo $sampe['acf_is_public'];

// 今バージョン移行
$sample = get_acf_fields( get_the_ID(), config('acf.news'));
echo $sampe['acf_is_public'];
```

## 2026.3.15

### viewsディレクトリのドキュメントを追加
1. 保守性の高いコードを書くために、意識してほしいことを記載しました。

- [VIEWSの記載について](https://github.com/allmanage-co-ltd/allmanage_theme_v2/blob/master/views/README.md)

### scssの記載ルールの文言を少し変更
1. 意図が分かりずらい文言を修正しました。

- [SASSの記載について](https://github.com/allmanage-co-ltd/allmanage_theme_v2/blob/master/assets/scss/README.md)

### `img_dir`関数の関数名を`img_uri`に変更
1. 以前のテーマから慣れていたので`img_dir`を続投していましたが、やはりWEBアクセス用なのにdirは違和感があるのでこのタイミングで変更しました。


### `MwWpForm.php`のミス修正
1. カスタムメールタグ定義のフックにミスがあり、フォームがエラーになっていたため修正しました。
2. フッターへスクリプトを追記するフック内のJSにタイポがあり修正しました。
3. フォーム作成時の自動返信メールの初期値を以前のテンプレートのものへ変更しました。

### `views/contact/index.php`にコメントを追加
```html
<div class="c-form">
    <?php
    /**
     * reCAPTCHA for MW WP Formを使用しない場合はdo_shortcodeを使用可能
     */
    // echo do_shortcode('[mwform_formkey key=""]');

    /**
     * reCAPTCHA for MW WP Formを使用する場合
     *
     * プラグインの仕様で固定ページにショートコードを記載し、the_contentを経由しないければ
     * reCAPTCHAの検証が発火しないため、do_shortcodeは使用できない。
     *
     * 後からreCAPTCHAを追加したい場合に仕様を知らないとドツボにハマる（経験談）ので
     * 基本的には固定ページにショートコードを記載しthe_contentするのを推奨。
     */
    the_content();
    ?>
</div>
```

### `config`ディレクトリ内を微変更
1. `cms.php`から`client_menu`を`menu.php`に切り分けました。管理画面メニューの表示に関する設定はこちらに移行します。
2. `seo.php`に`use_all_in_one_seo`項目を追加しました。
    - 社の方針でAll In One SEOが必須になる可能性があるため、テーマ側のメタタグの出力を無効にできるようにするため

### `the_breadcrumb`関数の出力HTMLを変更
1. l-breadcrumbクラスを追加、それに伴いテンプレートを修正しました。
```html
<div class="l-breadcrumb">
    <div class="c-inner">
        <ul class="l-breadcrumb_list">
            <li><a href="/">TOP</a></li>
            <li><a href="/news">NEWS</a></li>
        </ul>
    </div>
</div>
```

### `the_postnavi`関数を追加
1. single.phpの、前の記事、一覧へ戻る、次の記事を出力する関数を追加しました。
```php
function the_postnavi(
    string $archive_url = '/news',
    string $archive_text = '一覧へ戻る',
    string $prev_text = '← 前へ',
    string $next_text = '次へ →',
): void {
    (new \App\WordPress\Presenter\PostNavigation($archive_url, $archive_text, $prev_text, $next_text))->render();
}
```
```html
<?php the_postnavi( url('news'), 'NEWS一覧', '前の記事', '次の記事'); ?>

<div class="wp-postnavi">
    <a href="..." class="wp-postnavi__prev">
        <span class="wp-postnavi__txt">前の記事</span>
    </a>
    <a href="..." class="wp-postnavi__archive">
        NEWS一覧
    </a>
    <a href="..." class="wp-postnavi__next">
        <span class="wp-postnavi__txt">次の記事</span>
    </a>
</div>
```

### `get_acf_action`関数を追加
1. `views`の中で`get_field`関数を何度を呼びたくないという理由と、`get_field`や`the_field`は呼ぶ毎にDBへ毎回アクセスするのを回避するため、ACF取得用の関数を追加しました。`Acf::getByKeys`の内部で`get_fields`を使用して一度にすべてのフィールドを取得し、値をキャッシュしています。
```php
// viewsの中
$sample = get_acf_action( get_the_ID() )->handle();
echo $sampe['acf_is_public']; // 配列キーは実際のものに変更

// app/UseCase/GetAcfAction.php
final class GetAcfAction
{
    ...省略

    /**
     * ACFフィールド名
     */
    public function handle(): array
    {
        return Acf::getByKeys($this->post_id, [
            'acf_is_public', // ここに実際のACFフィールド名を追加していく
        ]);
    }
}
```

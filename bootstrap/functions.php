<?php

/**---------------------------------------------
 * グローバル関数
 *----------------------------------------------
 * viewから呼び出せる独自の関数はここにあるもののみです。
 *
 * 概要：
 * - 主にviewで呼び出す用のどこからでも呼べる関数
 * - ここでは「判断・ロジック・状態」を持たないようにする
 * - 実処理は必ず app ディレクトリの適切なクラスに逃がす（なければ作成）
 * - 簡易な処理であれば許容します。（5行くらいまで）
 *
 * ルール：
 * - 引数と返り値のシグネチャは呼び出し先と一致させる
 * - echoやincludeなど描画系の関数は命名を 「the_○○」 とする
 *
 * - ※必ず何をする関数か明記し「使用例」を記載すること
 * - ※関数の追加は上から（案件ごとに消しやすいため）
 *---------------------------------------------/

 /**
 * ACF一括取得クラスのインスタンス
 * 使用する際は定義元のクラスメソッドをカスタムしてください。
 *
 * 使用例:
 *   $sample = get_acf( get_the_ID() )->handle();
 *   $sampe['acf_is_public'];
 *
 * カスタムフィールドが集約した配列が返ります。
 * 配列のキーはそのままカスタムフィールドのキーです。
 */
function get_acf(int $post_id): \App\Project\GetAcfFields
{
    return new \App\Project\GetAcfFields($post_id);
}

/**---------------------------------------------
 *
 * 以下デフォルト
 *
 *---------------------------------------------/

/**
 * サイトのルートURI
 */
function home(): string
{
    return home_url();
}

/**
 * テーマのURI（末尾スラッシュなし）
 */
function theme_uri(): string
{
    return rtrim(get_template_directory_uri(), '/');
}

/**
 * テーマのパス（末尾スラッシュなし）
 */
function theme_dir(): string
{
    return rtrim(get_template_directory(), '/');
}

/**
 * 画像ディレクトリURI（末尾スラッシュなし）
 *
 * 本当はimg_uriにしたいが、過去テーマからの流用で慣れてるので一旦このまま
 */
function img_uri(): string
{
    return theme_uri() . '/assets/img';
}

/**
 * WP_Query ビルダー取得
 *
 * デバッグをするには->debug()を呼ぶとargsの中身が見れます
 *
 * 使用例:
 *   wpquery()->setPostType(...)->setPerPage(...)->build();
 *   wpquery()->setPostType(...)->setPerPage(...)->debug();
 */
function wpquery(): \App\WordPress\Wrapper\MyWpQuery
{
    return \App\WordPress\Wrapper\MyWpQuery::new();
}

/**
 * 設定値取得
 *
 * 使用例:
 *   echo config('seo.name');
 */
function config(string $key, $default = null)
{
    return \App\Shared\Config::get($key, $default);
}

/**
 * permalink 設定からURL取得
 *
 * 使用例:
 *   echo url('news');
 */
function url(string $slug): string
{
    return \App\Shared\Config::get("permalink.{$slug}", '/');
}

/**
 * wpdbのラッパー
 *
 * WPテーマではあまり使わなそう
 *
 * 使用例:
 *   db()->stmt('...', [arg])->debug();        ←組み立てたSQLの出力のみ
 *   db()->stmt('SELECT * FROM wp_posts WHERE ID = %d', [1])->get();
 *   db()->stmt('...', [arg])->select();
 *   db()->stmt('...', [arg])->execute();
 */
function db(): \App\WordPress\Wrapper\MyWpDb
{
    return \App\WordPress\Wrapper\MyWpDb::new();
}

/**
 * flatpickrの初期化
 *
 * js-datepickerクラスが付与されたテキストフィールドに対してデートピッカーが自動で入れ込まれる。
 * 有効にしたいページで関数を実行することで有効化。
 *
 * 使用例（お問い合わせ入力ページで）:
 *   datepicker();
 */
function datepicker(array $options = []): void
{
    (new \App\WordPress\Presenter\Datepicker($options))->boot();
}

/**
 * 念のためロガー
 *
 * 使用例:
 *   slog()->info('message', $data); >> logs/app.log
 */
function slog()
{
    return \App\Shared\Logger::app();
}

/**
 * セッション関連ヘルパー
 *
 * 使用例:
 *   sess()->set('user_id', 1);
 *   $id = sess()->get('user_id');
 *   sess()->forget('user_id');
 *   sess()->flash('message', '送信しました');
 *   $message = sess()->pull('message');
 */
function sess(): \App\Shared\Session
{
    return new \App\Shared\Session();
}

/**
 * curlを用いたhttpリクエストヘルパー
 *
 * 使用例:
 *   $res = curl('POST', 'https://api.example.com/users');
 *   if ($res->ok()) {
 *       d($res->body());
 *   }
 */
function curl(string $method, string $url, array $options = []): \App\Shared\Curl
{
    return \App\Shared\Curl::request($method, $url, $options);
}

/**
 * ローカル環境判定
 *
 * 使用例:
 *   if (is_local()) {...}
 */
function is_local(): bool
{
    return \App\Shared\Runtime::isLocal();
}

/**
 * モバイル判定
 *
 * 使用例:
 *   if (is_mobile()) {...}
 */
function is_mobile(): bool
{
    return \App\Shared\Runtime::isMobile();
}

/**
 * Bot 判定
 *
 * 使用例:
 *   if (is_bot()) {...}
 */
function is_bot(): bool
{
    return \App\Shared\Runtime::isBot();
}

/**
 * ビューの描画
 *
 * header + view + footer を一括で処理
 * ページ、アーカイブ、タクソノミー、シングル、サーチを
 * App\WordPress\Presenter\View側で判定し、呼ぶテンプレートを切り替えています。
 *
 * 使用例（テンプレートページで）:
 *   the_view();
 */
function the_view(): void
{
    \App\WordPress\Presenter\View::pages();
}

/**
 * レイアウトファイル描画
 *
 * 使用例:
 *   the_layout('header');
 */
function the_layout(string $name): void
{
    \App\WordPress\Presenter\View::layout($name);
}

/**
 * コンポーネント描画
 *
 * get_template_partのラッパーなので引数を渡せます。
 * コンポーネント側で  $args['hoge']  で受け取ります。
 *
 * 使用例:
 *   the_component('searchform', ['hoge' => $fuga]);
 */
function the_component(string $name, array $data = []): void
{
    \App\WordPress\Presenter\View::component($name, $data);
}

/**
 * パンくずリスト描画
 *
 * 使用例:
 *   the_breadcrumb();
 *
 * <div class="l-breadcrumb">
 *     <div class="c-inner">
 *         <ul class="l-breadcrumb_list">
 *             <li><a href="/">TOP</a></li>
 *             <li><a href="/news">NEWS</a></li>
 *         </ul>
 *     </div>
 * </div>
 */
function the_breadcrumb(): void
{
    (new \App\WordPress\Presenter\Breadcrumb)->render();
}

/**
 * ページネーション出力
 *
 * 使用例:
 *   the_pagination($query, 3);
 *
 * 出力HTML:
 *   <div class="wp-pager">
 *     <ul class="wp-pager__list">
 *       <li class="wp-pager__item -first">
 *         <a href="https://example.com/page/2/" class="prev page-numbers">←</a>
 *       </li>
 *       <li class="wp-pager__item -current current">
 *         <a href="https://example.com/page/3/" class="page-numbers">1</a>
 *       </li>
 *       <li class="wp-pager__item -last">
 *         <a href="https://example.com/page/4/" class="next page-numbers">→</a>
 *       </li>
 *     </ul>
 *   </div>
 */
function the_pagination(\WP_Query $query, int $range = 5, string $prev_text = '←', string $next_text = '→'): void
{
    (new \App\WordPress\Presenter\Pagination($query, $range, $prev_text, $next_text))->render();
}

/**
 * single.phpの記事ナビゲーションを出力
 *
 * 使用例:
 *   the_postnavi( url('news'), 'NEWS一覧', '前の記事', '次の記事');
 *
 * 出力HTML:
 *   <div class="wp-postnav">
 *       <a href="https://example.com/news/12" class="wp-postnav__prev">
 *           <span class="wp-postnav__txt">前の記事</span>
 *       </a>
 *       <a href="https://example.com/news/" class="wp-postnav__archive">
 *           NEWS一覧
 *       </a>
 *       <a href="https://example.com/news/14" class="wp-postnav__next">
 *           <span class="wp-postnav__txt">次の記事</span>
 *       </a>
 *   </div>
 */
function the_postnavi(
    string $archive_url = '/news',
    string $archive_text = '一覧へ戻る',
    string $prev_text = '← 前へ',
    string $next_text = '次へ →',
): void {
    (new \App\WordPress\Presenter\PostNavigation($archive_url, $archive_text, $prev_text, $next_text))->render();
}

/**
 * Cookieのモーダル表示
 *
 * 使用例:
 *   the_cookie_modal(60, url('privacy));
 */
function the_cookie_modal($days = 365, $link = '/privacy'): void
{
    (new \App\WordPress\Presenter\Cookie($days, $link))->render();
}

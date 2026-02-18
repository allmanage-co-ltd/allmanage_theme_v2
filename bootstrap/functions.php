<?php

/**---------------------------------------------
 * グローバル関数群
 *----------------------------------------------
 * - 主にviewで使うどこからでも呼べる関数
 * - ここでは「判断・ロジック・状態」を持たない
 * - 実処理は必ず app ディレクトリの適切なクラスに逃がす（なければ作成）
 * - 簡易な処理であれば許容しています。（5行くらいまでかな。。）
 *
 * ルール：
 * - 引数と返り値のシグネチャは呼び出し先と一致させる
 * - echoやincludeなど描画系の関数は命名を 「the_○○」 とする
 * - WP関数のラッパーでも OK
 * - 必ず「使用例」を記載すること
 *---------------------------------------------/

/**
 * サイトのルートURLを返す
 * WPの home_url() のラッパー
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
 * テーマの物理パス（末尾スラッシュなし）
 */
function theme_dir(): string
{
    return rtrim(get_template_directory(), '/');
}

/**
 * 画像ディレクトリURI（末尾スラッシュなし）
 */
function img_dir(): string
{
    return theme_uri() . '/assets/img';
}

/**
 * WP_Query ビルダー取得
 *
 * デバッグをするには->debug()を呼ぶとargsの中身が見れる
 *
 * 使用例:
 *   wpquery()->setPostType(...)->setPerPage(...)->build()
 */
function wpquery(): \App\CMS\Wrapper\MyWpQuery
{
    return \App\CMS\Wrapper\MyWpQuery::new();
}

/**
 * wpdbのラッパー
 *
 * WPテーマではあまり使わなそう。。
 *
 * 使用例:
 *   db()->stmt('...', [arg])->debug();        ←組み立てたSQLの出力のみ
 *   db()->stmt('SELECT * FROM wp_posts WHERE ID = %d', [1])->get();
 *   db()->stmt('...', [arg])->select();
 *   db()->stmt('...', [arg])->execute();
 */
function db(): \App\CMS\Wrapper\MyDpdb
{
    return \App\CMS\Wrapper\MyDpdb::new();
}

/**
 * flatpickrの初期化
 *
 * js-datepickerクラスが付与されたテキストフィールドに対して
 * デートピッカーが自動で入れ込まれる。
 * 有効にしたいページで関数を実行することで有効化。
 *
 * 使用例（お問い合わせ入力ページで）:
 *   datepicker();
 */
function datepicker(array $options = []): void
{
    (new \App\CMS\Presenter\Datepicker($options))->boot();
}

/**
 * ビューの描画
 *
 * header → view → footer を一括で処理
 * ページ、アーカイブ、タクソノミー、シングル、サーチを
 * App\CMS\Presenter\View側で判定し、呼ぶviewを切り替えています。
 *
 * 使用例（テンプレートページで）:
 *   the_view();
 */
function the_view(): void
{
    \App\CMS\Presenter\View::pages();
}

/**
 * レイアウトファイル描画
 *
 * 使用例:
 *   the_layout('header');
 */
function the_layout(string $name): void
{
    \App\CMS\Presenter\View::layout($name);
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
    \App\CMS\Presenter\View::component($name, $data);
}

/**
 * パンくずリスト描画
 *
 * 使用例:
 *   the_breadcrumb();
 */
function the_breadcrumb(): void
{
    (new \App\CMS\Presenter\Breadcrumb)->render();
}

/**
 * Cookieのモーダル表示
 *
 * 使用例:
 *   the_cookie_modal(60, url('privacy));
 */
function the_cookie_modal($days = 365, $link = '/privacy'): void
{
    (new \App\CMS\Presenter\Cookie($days, $link))->render();
}

/**
 * ページネーション出力
 *
 * 吐き出すHTMLはpw_paginateと同じはず。。
 *
 * 使用例:
 *   the_pagination($query, 3);
 */
function the_pagination(\WP_Query $query, int $range = 5): void
{
    (new \App\CMS\Presenter\Pagination($query, $range))->render();
}

/**
 * 設定値取得
 *
 * 使用例:
 *   echo config('site.name');
 */
function config(string $key, $default = null)
{
    return \App\Services\Config::get($key, $default);
}

/**
 * permalink 設定からURL取得
 *
 * 使用例:
 *   echo url('news');
 */
function url(string $slug): string
{
    return \App\Services\Config::get("permalink.{$slug}", '/');
}

/**
 * ロガー
 *
 * 使用例:
 *   slog()->info('message', $data); >> logs/app.log
 */
function slog()
{
    return \App\Services\Logger::new();
}

/**
 * セッション関連のヘルパー
 *
 * 使用例:
 *   sess()->set('user_id', 1);
 *   $id = sess()->get('user_id');
 *   sess()->forget('user_id');
 *   sess()->flash('message', '送信しました');
 *   $message = sess()->pull('message');
 */
function sess(): \App\Services\Session
{
    return new \App\Services\Session();
}

/**
 * curlを用いたhttpリクエストのヘルパー
 *
 * 使用例:
 *   $res = curl('POST', 'https://api.example.com/users');
 *   if ($res->ok()) {
 *       d($res->body());
 *   }
 */
function curl(string $method = 'GET', string $url, array $options = []): \App\Services\Curl
{
    return \App\Services\Curl::request($method, $url, $options);
}


/**
 * ローカル環境判定
 *
 * 使用例:
 *   if (is_local()) {...}
 */
function is_local(): bool
{
    return \App\Services\Runtime::isLocal();
}

/**
 * モバイル判定
 *
 * 使用例:
 *   if (is_mobile()) {...}
 */
function is_mobile(): bool
{
    return \App\Services\Runtime::isMobile();
}

/**
 * Bot 判定
 *
 * 使用例:
 *   if (is_bot()) {...}
 */
function is_bot(): bool
{
    return \App\Services\Runtime::isBot();
}
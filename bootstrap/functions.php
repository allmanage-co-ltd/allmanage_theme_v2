<?php
/**---------------------------------------------
 * グローバル関数群
 *----------------------------------------------
 * - viewで使うようのどこからでも呼べる関数
 * - ここでは「判断・ロジック・状態」を持たない
 * - 実処理は必ず app ディレクトリの適切なクラスに逃がす（なければ作成）
 * - 簡易な処理であれば許容しています。（5行くらいまでかな。。）
 *
 * ルール：
 * - 引数と返り値のシグネチャは呼び出し先と一致させる
 * - echoやincludeなど描画系の関数は命名を 「the_○○」 とする
 * - WP関数のラッパーでも OK
 *
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
 * 設定値取得
 * echo config('site.name')
 */
function config(string $key, $default = null)
{
    return \App\Services\Config::get($key, $default);
}

/**
 * permalink 設定からURL取得
 * echo url('news')
 */
function url(string $slug): string
{
    return \App\Services\Config::get("permalink.{$slug}", home());
}

/**
 * WP_Query ビルダー取得
 * デバッグをするには->build()を呼ぶとargsの中身が見れる
 * wpquery()->setPostType(...)->setPerPage(...)->build()
 */
function wpquery(): \App\Services\MyWpQuery
{
    return \App\Services\MyWpQuery::new();
}

/**
 * flatpickrの初期化
 * js-datepickerクラスが付与されたテキストフィールドに対して
 * デートピッカーが自動で入れ込まれる。有効にしたいページで関数を実行することで有効化。
 */
function datepicker(array $options = []): void
{
    (new \App\Views\Datepicker($options))->boot();
}

/**
 * ロガー
 * slog()->info('message', $data) >> logs/app.log
 */
function slog()
{
    return \App\Services\Logger::new();
}

/**
 * wpdbのラッパー
 * WPテーマではあまり使わなそう。。
 * db()->stmt('...', [arg])->debug();        ←組み立てたSQLの出力のみ
 * db()->stmt('SELECT * FROM wp_posts WHERE ID = %d', [1])->get();
 * db()->stmt('...', [arg])->select();
 * db()->stmt('...', [arg])->execute();
 */
function db(): \App\Databases\Database
{
    return \App\Databases\Database::new();
}

/**
 * ビューの描画
 * header → view → footer を一括で処理
 * ページ、アーカイブ、タクソノミー、シングル、サーチを
 * Service側で判定し、呼ぶviewを切り替えています。
 */
function the_view(): void
{
    \App\Views\Render::pages();
}

/**
 * レイアウトファイル描画
 * the_layout('header')
 */
function the_layout(string $name): void
{
    \App\Views\Render::layout($name);
}

/**
 * コンポーネント描画
 * the_component('searchform', ['hoge' => $fuga])
 */
function the_component(string $name, array $data = []): void
{
    \App\Views\Render::component($name, $data);
}

/**
 * パンくずリスト描画
 */
function the_breadcrumb(): void
{
    (new \App\Views\Breadcrumb)->render();
}

/**
 * Cookieのモーダル表示
 */
function the_cookie_modal($days = 365, $link = '/privacy'): void
{
    (new \App\Views\Cookie($days, $link))->render();
}

/**
 * ページネーション出力
 * 吐き出すHTMLはpw_paginateと同じはず。。
 */
function the_pagination(\WP_Query $query, int $range = 5): void
{
    (new \App\Views\Pagination($query, $range))->render();
}

/**
 * ローカル環境判定
 */
function is_local(): bool
{
    return \App\Services\Environment::isLocal();
}

/**
 * モバイル判定
 */
function is_mobile(): bool
{
    return \App\Services\Environment::isMobile();
}

/**
 * Bot 判定
 */
function is_bot(): bool
{
    return \App\Services\Environment::isBot();
}
<?php

namespace App\Presenters;

use App\Helpers\Arr;

/**---------------------------------------------
 * ビュー描画クラス
 * ---------------------------------------------
 * - WordPress の条件分岐を集約して表示ファイルを決定
 * - header / footer / 本体ビューの組み立てを一元管理
 */
class View
{
    /**
     * コンポーネント描画
     */
    public static function component(string $name, array $data = []): void
    {
        get_template_part("views/component/{$name}", $name, $data);
    }

    /**
     * レイアウト読み込み
     */
    public static function layout(string $name): void
    {
        get_template_part("views/layout/{$name}");
    }

    /**
     * ページ全体描画
     */
    public static function pages(): void
    {
        $header = theme_dir() . '/header.php';
        $footer = theme_dir() . '/footer.php';

        $file = self::resolve();

        if (!$file || !\file_exists($file)) {
            $file = theme_dir() . '/views/page/404.php';
        }

        foreach ([$header, $file, $footer] as $currentFile) {
            include_once $currentFile;
        }
    }

    /**
     * 表示ファイル解決
     */
    private static function resolve(): ?string
    {
        if (is_front_page()) {
            return theme_dir() . '/views/page/home.php';
        }

        if (is_search()) {
            return theme_dir() . '/views/page/search.php';
        }

        if (is_page()) {
            return self::page();
        }

        if (is_single()) {
            return self::single();
        }

        if (is_post_type_archive()) {
            return self::archive();
        }

        if (is_tax() || is_category() || is_tag()) {
            return self::tax();
        }

        return null;
    }

    /**
     * 固定ページ用ビュー解決
     */
    private static function page(): ?string
    {
        global $post;

        if (!$post) {
            return null;
        }

        $base  = theme_dir() . '/views/page/';
        $slugs = [];

        if ($ancestors = get_post_ancestors($post->ID)) {
            foreach (\array_reverse($ancestors) as $ancestor) {
                $slugs[] = get_post($ancestor)->post_name;
            }
        }

        $slugs[] = $post->post_name;

        $path = $base . \implode('/', $slugs) . '/index.php';
        if (\file_exists($path)) {
            return $path;
        }

        $path = $base . \implode('/', $slugs) . '.php';
        if (\file_exists($path)) {
            return $path;
        }

        $child = Arr::last($slugs);
        if (!\is_string($child)) {
            return null;
        }

        $path = $base . $child . '/index.php';
        if (\file_exists($path)) {
            return $path;
        }

        $path = $base . $child . '.php';
        if (\file_exists($path)) {
            return $path;
        }

        $path = $base . 'index.php';
        if (\file_exists($path)) {
            return $path;
        }

        $path = theme_dir() . '/views/page.php';
        if (\file_exists($path)) {
            return $path;
        }

        return null;
    }

    /**
     * 投稿タイプアーカイブ用ビュー解決
     */
    private static function archive(): ?string
    {
        $postType = get_query_var('post_type');
        $path     = theme_dir() . "/views/archive/{$postType}.php";

        return \file_exists($path) ? $path : null;
    }

    /**
     * 投稿詳細ページ用ビュー解決
     */
    private static function single(): ?string
    {
        $postType = get_post_type();
        $path     = theme_dir() . "/views/single/{$postType}.php";

        return \file_exists($path) ? $path : null;
    }

    /**
     * タクソノミー・カテゴリ・タグ用ビュー解決
     */
    private static function tax(): ?string
    {
        $term = get_queried_object();

        $path = theme_dir() . "/views/taxonomy/{$term->taxonomy}.php";
        if (\file_exists($path)) {
            return $path;
        }

        $path = theme_dir() . "/views/taxonomy/{$term->taxonomy}/{$term->slug}.php";
        if (\file_exists($path)) {
            return $path;
        }

        return null;
    }
}

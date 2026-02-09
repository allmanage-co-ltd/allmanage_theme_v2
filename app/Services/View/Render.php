<?php

namespace App\Services\View;

/**---------------------------------------------
 * ビュー描画サービス
 * ---------------------------------------------
 * - WordPress の条件分岐を集約して表示ファイルを決定
 * - header / footer / 本体ビューの組み立てを一元管理
 */
class Render
{
  public function __construct()
  {
    //
  }

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
   *
   * - header / footer を含めて1ページを構築
   * - resolve() で決定したビューを中央に挿入
   * - 見つからない場合は 404 を表示
   */
  public static function pages(): void
  {
    $header = theme_dir() . '/header.php';
    $footer = theme_dir() . '/footer.php';

    $file = self::resolve();

    if (!$file || !file_exists($file)) {
      $file = theme_dir() . '/views/page/404.php';
    }

    foreach ([$header, $file, $footer] as $f) {
      include_once $f;
    }
  }

  /**
   * 表示ファイル解決
   */
  private static function resolve(): ?string
  {
    // トップページ
    if (is_front_page()) {
      return theme_dir() . '/views/page/index.php';
    }

    // 検索ページ
    if (is_search()) {
      return theme_dir() . '/views/page/search.php';
    }

    // 固定ページ
    if (is_page()) {
      return self::page();
    }

    // 投稿詳細ページ
    if (is_single()) {
      return self::single();
    }

    // 投稿タイプアーカイブ
    if (is_post_type_archive()) {
      return self::archive();
    }

    // タクソノミーアーカイブ
    if (is_tax() || is_category() || is_tag()) {
      return self::tax();
    }

    return null;
  }

  /**
   * 固定ページ用ビュー解決
   *
   * - 親子階層を考慮した探索順を定義
   * - 深い階層ほど具体的なテンプレを優先
   *
   * 探索順：
   * 1. 親/子/index.php
   * 2. 親/子.php
   * 3. 子/index.php
   * 4. 子.php
   * 5. page/index.php
   * 6. page.php
   */
  private static function page(): ?string
  {
    global $post;

    if (!$post) {
      return null;
    }

    $base  = theme_dir() . '/views/page/';
    $slugs = [];

    // 親ページを上から積む
    if ($ancestors = get_post_ancestors($post->ID)) {
      foreach (array_reverse($ancestors) as $ancestor) {
        $slugs[] = get_post($ancestor)->post_name;
      }
    }

    // 自分の slug
    $slugs[] = $post->post_name;

    // 1. 親/子/index.php
    $path = $base . implode('/', $slugs) . '/index.php';
    if (file_exists($path)) {
      return $path;
    }

    // 2. 親/子.php
    $path = $base . implode('/', $slugs) . '.php';
    if (file_exists($path)) {
      return $path;
    }

    // 3. 子/index.php
    $child = end($slugs);
    $path  = $base . $child . '/index.php';
    if (file_exists($path)) {
      return $path;
    }

    // 4. 子.php
    $path = $base . $child . '.php';
    if (file_exists($path)) {
      return $path;
    }

    // 5. page/index.php
    $path = $base . 'index.php';
    if (file_exists($path)) {
      return $path;
    }

    // 6. page.php
    $path = theme_dir() . '/views/page.php';
    if (file_exists($path)) {
      return $path;
    }

    return null;
  }

  /**
   * 投稿タイプアーカイブ用ビュー解決
   *
   * - views/archive/{post_type}.php を探す
   */
  private static function archive(): ?string
  {
    $post_type = get_query_var('post_type');

    $path = theme_dir() . "/views/archive/{$post_type}.php";

    return file_exists($path) ? $path : null;
  }

  /**
   * 投稿詳細ページ用ビュー解決
   *
   * - views/single/{post_type}.php を探す
   */
  private static function single(): ?string
  {
    $post_type = get_post_type();

    $path = theme_dir() . "/views/single/{$post_type}.php";

    return file_exists($path) ? $path : null;
  }

  /**
   * タクソノミー・カテゴリ・タグ用ビュー解決
   *
   * 探索順：
   * 1. taxonomy/{taxonomy}.php
   * 2. taxonomy/{taxonomy}/{term}.php
   */
  private static function tax(): ?string
  {
    $term = get_queried_object();

    // taxonomy 単位
    $path = theme_dir() . "/views/taxonomy/{$term->taxonomy}.php";
    if (file_exists($path)) {
      return $path;
    }

    // taxonomy + term
    $path = theme_dir() . "/views/taxonomy/{$term->taxonomy}/{$term->slug}.php";
    if (file_exists($path)) {
      return $path;
    }

    return null;
  }
}
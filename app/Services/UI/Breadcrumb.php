<?php

namespace App\Services\UI;

/**---------------------------------------------
 * パンくずリスト生成クラス
 * ---------------------------------------------
 * - パンくずリストを生成する
 * - 表示ロジックをテンプレートから分離する
 */
class Breadcrumb
{
  // パンくず項目格納用
  private array $items = [];

  public function __construct()
  {
    //
  }

  /**
   * パンくずHTML生成
   */
  public function render(): string
  {
    if (is_home() || is_front_page() || is_admin())
      return '';

    $this->breadcrumb();

    return <<<HTML
  <ul class="l-breadcrumb_list">
    {$this->implode()}
  </ul>
HTML;
  }

  /**
   * パンくず項目を連結
   */
  private function implode(): string
  {
    return implode("\n", $this->items);
  }

  /**
   * パンくず構築処理
   */
  private function breadcrumb(): void
  {
    global $post;

    $this->items[] = '<li><a href="' . home_url('/') . '">TOP</a></li>';

    switch (true) {
      case is_search():
        $this->items[] = '<li>「' . get_search_query() . '」の検索結果</li>';
        break;

      case is_tag():
        $this->items[] = '<li>タグ : ' . single_tag_title('', false) . '</li>';
        break;

      case is_404():
        $this->items[] = '<li>404 Not found</li>';
        break;

      case is_date():
        $year = get_query_var('year');
        $month = get_query_var('monthnum');
        $day = get_query_var('day');

        if ($day) {
          $this->items[] = '<li><a href="' . get_year_link($year) . '">' . $year . '年</a></li>';
          $this->items[] = '<li><a href="' . get_month_link($year, $month) . '">' . $month . '月</a></li>';
          $this->items[] = '<li>' . $day . '日</li>';
        } elseif ($month) {
          $this->items[] = '<li><a href="' . get_year_link($year) . '">' . $year . '年</a></li>';
          $this->items[] = '<li>' . $month . '月</li>';
        } else {
          $this->items[] = '<li>' . $year . '年</li>';
        }
        break;

      case is_category():
        $cat = get_queried_object();
        $catId = $cat->parent
          ? array_reverse(get_ancestors($cat->term_id, 'category'))[0]
          : $cat->term_id;

        $this->items[] = '<li>' . get_cat_name($catId) . '</li>';
        break;

      case is_author():
        $this->items[] = '<li>' . get_the_author_meta('display_name', get_query_var('author')) . '</li>';
        break;

      case is_page():
        if ($post->post_parent) {
          foreach (array_reverse(get_post_ancestors($post->ID)) as $ancestor) {
            $this->items[] =
              '<li><a href="' . get_permalink($ancestor) . '">' .
              get_the_title($ancestor) .
              '</a></li>';
          }
        }
        $this->items[] = '<li>' . $post->post_title . '</li>';
        break;

      case is_attachment():
        if ($post->post_parent) {
          $this->items[] =
            '<li><a href="' . get_permalink($post->post_parent) . '">' .
            get_the_title($post->post_parent) .
            '</a></li>';
        }
        $this->items[] = '<li>' . $post->post_title . '</li>';
        break;

      case is_single():
        if (is_singular('post')) {
          $categories = get_the_category($post->ID);
          if ($categories) {
            $cat = $categories[0];
            if ($cat->parent) {
              $ancestors = array_reverse(get_ancestors($cat->cat_ID, 'category'));
              $cat       = get_category($ancestors[0]);
            }
            $this->items[] =
              '<li><a href="' . home_url('/news/') . '">' .
              $cat->name .
              '</a></li>';
          }
        } else {
          $obj           = get_post_type_object(get_post_type());
          $this->items[] =
            '<li><a href="' . get_post_type_archive_link($obj->name) . '">' .
            $obj->label .
            '</a></li>';
        }
        $this->items[] = '<li>' . $post->post_title . '</li>';
        break;

      case is_tax():
      case is_archive():
        $term = get_queried_object();
        $taxonomy = $term->taxonomy ?? null;
        $postType = $taxonomy
          ? get_taxonomy($taxonomy)->object_type[0]
          : get_post_type();

        if ($postType) {
          $obj           = get_post_type_object($postType);
          $this->items[] =
            '<li><a href="' . get_post_type_archive_link($postType) . '">' .
            $obj->label .
            '</a></li>';
        }

        if (!empty($term->term_id)) {
          if ($term->parent) {
            foreach (array_reverse(get_ancestors($term->term_id, $term->taxonomy)) as $ancestor) {
              $this->items[] =
                '<li><a href="' . get_term_link($ancestor) . '">' .
                get_term($ancestor)->name .
                '</a></li>';
            }
          }
          $this->items[] = '<li>' . $term->name . '</li>';
        }
        break;

      default:
        $this->items[] = '<li>' . wp_title('', false) . '</li>';
    }
  }
}
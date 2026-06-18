<?php

namespace App\Hooks;

use App\Interfaces\BootableWpHookInterface;
use App\Services\Config;

/**
 * 検索フィルター共通処理
 */
class AddSearchFromFilter implements BootableWpHookInterface
{
  public function boot(): void
  {
    if (!config('searchform.use_add_filter')) return;

    add_filter('posts_where', $this->filter(...), 10, 2);
  }

  /**
   * WHERE句を書き換える
   */
  public function filter(string $search, \WP_Query $query): string
  {
    if (is_admin() || !$query->is_search()) {
      return $search;
    }

    global $wpdb;

    $keyword = $query->get('s');
    if (!$keyword) return $search;

    $like = '%' . $wpdb->esc_like($keyword) . '%';

    $postTypes = (array) $query->get('post_type');
    $config    = config('searchform.filter');

    if ($postTypes === ['any'] || empty($postTypes)) {
      $postTypes = \array_keys($config);
    }

    $conditions = [];

    foreach ($postTypes as $postType) {

      if (!isset($config[$postType])) continue;

      $setting = $config[$postType];

      foreach (($setting['add_meta_keys'] ?? []) as $key) {
        $conditions[] = $wpdb->prepare(
          "EXISTS (
                    SELECT 1 FROM {$wpdb->postmeta} pm
                    WHERE pm.post_id = {$wpdb->posts}.ID
                    AND pm.meta_key = %s
                    AND pm.meta_value LIKE %s
                )",
          $key,
          $like
        );
      }

      if (!empty($setting['add_taxonomies'])) {
        $conditions[] = $wpdb->prepare(
          "EXISTS (
                    SELECT 1 FROM {$wpdb->term_relationships} tr
                    INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                    INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                    WHERE tr.object_id = {$wpdb->posts}.ID
                    AND t.name LIKE %s
                )",
          $like
        );
      }
    }
    // d('postTypes: ' . print_r($postTypes, true));
    // d('config keys: ' . print_r(array_keys($config), true));
    // d('conditions: ' . print_r($conditions, true));
    if ($conditions) {
      $custom = " (" . implode(' OR ', $conditions) . ") ";

      $where = preg_replace(
        "/\(\s*{$wpdb->posts}\.post_title\s+LIKE\s+.+?\)\)/s",
        '$0 OR ' . $custom,
        $search
      );

      if ($where === null) $where = $search;
    }

    // d('final search: ' . $search);
    return $where ?? $search;
  }
}

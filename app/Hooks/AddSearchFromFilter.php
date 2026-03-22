<?php

namespace App\Hooks\Abstracts;

use App\Interfaces\BootableWpHookInterface;

/**
 * 検索フィルター共通処理
 *
 * - configベースで検索条件を追加する
 */
class AddSearchFromFilter implements BootableWpHookInterface
{
    public function boot(): void
    {
        add_filter('posts_where', $this->filter(...), 10, 2);
    }

    /**
     * WHERE句を書き換える
     */
    public function filter(string $where, \WP_Query $query): string
    {
        if (is_admin()) return $where;

        $config = config('searchfilter');

        $postType = $query->get('post_type');

        // post_typeが配列対応（念のため）
        if (is_array($postType)) {
            $postType = $postType[0] ?? null;
        }

        // configに存在しない投稿タイプは対象外
        if (!$postType || !isset($config[$postType])) {
            return $where;
        }

        $setting = $config[$postType];

        $search = $query->get('s');
        if (!$search) return $where;

        global $wpdb;
        /** @var \wpdb $wpdb */

        $like = '%' . $wpdb->esc_like($search) . '%';

        $metaConditions = [];

        // meta検索
        foreach (($setting['add_meta_keys'] ?? []) as $key) {
            $metaConditions[] = $wpdb->prepare(
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

        if ($metaConditions) {
            $where .= " AND (" . implode(' OR ', $metaConditions) . ")";
        }

        // タクソノミー検索
        if (!empty($setting['add_taxonomies'])) {
            $where .= $wpdb->prepare(
                " OR EXISTS (
                    SELECT 1 FROM {$wpdb->term_relationships} tr
                    INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                    INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                    WHERE tr.object_id = {$wpdb->posts}.ID
                    AND t.name LIKE %s
                )",
                $like
            );
        }

        return $where;
    }
}

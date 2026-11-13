<?php

declare(strict_types=1);

namespace App\Hooks;

use App\Interfaces\BootableWpHookInterface;

/**---------------------------------------------
 * 投稿タイプ別の全件削除機能。
 * - 管理画面の投稿一覧（edit.php）に「全件削除」リンクを追加する
 * - Ajax ハンドラで 100 件ずつ削除しタイムアウトを回避する
 * - 削除実行画面は進捗バー付き（停止・再開可能）
 * - タクソノミー（カテゴリ・タグ等）のターム一覧プレビューと同時削除に対応
 * ---------------------------------------------
 * 対象: manage_options 権限ユーザーのみ
 */
class PurgePostsHook implements BootableWpHookInterface
{
    private const NONCE_ACTION       = 'purge_all_posts';
    private const AJAX_ACTION        = 'purge_all_posts';
    private const AJAX_ACTION_TERMS  = 'purge_all_posts_get_terms';
    private const BATCH_SIZE         = 100;
    private const BATCH_TIMEOUT      = 15; // 秒

    public function boot(): void
    {
        // 管理画面の投稿一覧（edit.php）にリンクを追加する。
        // current_screen が確定する load-edit.php のタイミングで対象タイプを取得し動的にフィルタを登録する。
        add_action('load-edit.php', function (): void {
            $screen    = get_current_screen();
            $post_type = $screen ? $screen->post_type : 'post';
            add_filter("views_edit-{$post_type}", $this->addPurgeLink(...));
        });

        // load-edit.php は edit.php の出力が始まる前に発火するため、ここで exit すれば確実にインターセプトできる
        add_action('load-edit.php', $this->handlePurgePage(...), 1);
        add_action('wp_ajax_' . self::AJAX_ACTION, $this->handleAjax(...));
        add_action('wp_ajax_' . self::AJAX_ACTION_TERMS, $this->handleGetTerms(...));
    }

    /**
     * 投稿一覧の「絞り込みリンク」行に全件削除リンクを追加する。
     *
     * @param array<string, string> $views
     * @return array<string, string>
     */
    public function addPurgeLink(array $views): array
    {
        if (!current_user_can('manage_options')) {
            return $views;
        }

        $screen    = get_current_screen();
        $post_type = $screen ? $screen->post_type : 'post';
        // edit.php ベースのURLにすることで WordPress の権限チェックをパスする
        $base = $post_type === 'post' ? 'edit.php' : "edit.php?post_type={$post_type}";
        $url  = esc_url(add_query_arg('do_purge', '1', admin_url($base)));

        $views['purge_all'] = sprintf(
            '<a href="%s" style="color:#b32d2e;font-weight:600;">全件削除</a>',
            $url,
        );

        return $views;
    }

    /**
     * 削除実行ページの描画。
     * load-edit.php で edit.php?do_purge=1 を処理する。
     * admin.php ベースだと WordPress が権限チェックで弾くため edit.php を使う。
     */
    private function handlePurgePage(): void
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (empty($_GET['do_purge'])) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $post_type = sanitize_key($_GET['post_type'] ?? 'post');
        if ($post_type === '') {
            $post_type = 'post';
        }

        if (!current_user_can('manage_options')) {
            wp_die('権限がありません');
        }

        // 存在する投稿タイプのみ受け付ける
        if (!post_type_exists($post_type)) {
            wp_die('不明な投稿タイプです');
        }

        $this->renderPurgePage($post_type);
        exit;
    }

    /**
     * この投稿タイプに紐づくタクソノミーとターム情報を返す。
     *
     * @param string $post_type
     * @return array<int, array{taxonomy: string, label: string, terms: list<array{id: int, name: string, count: int}>}>
     */
    private function getTaxonomyTerms(string $post_type): array
    {
        $taxonomies = get_object_taxonomies($post_type, 'objects');
        $result     = [];

        foreach ($taxonomies as $taxonomy) {
            $terms = get_terms([
                'taxonomy'   => $taxonomy->name,
                'hide_empty' => false,
                'number'     => 0,
            ]);

            if (is_wp_error($terms) || empty($terms)) {
                continue;
            }

            $term_list = [];
            foreach ($terms as $term) {
                $term_list[] = [
                    'id'    => (int) $term->term_id,
                    'name'  => $term->name,
                    'count' => (int) $term->count,
                ];
            }

            $result[] = [
                'taxonomy' => $taxonomy->name,
                'label'    => $taxonomy->labels->name,
                'terms'    => $term_list,
            ];
        }

        return $result;
    }

    /**
     * 削除進捗画面を出力する。WordPress 管理画面のラッパー内にビューを読み込む。
     */
    private function renderPurgePage(string $post_type): void
    {
        global $wpdb;

        $post_type_obj  = get_post_type_object($post_type);
        $label          = $post_type_obj ? $post_type_obj->labels->name : $post_type;
        // auto-draft は WordPress が内部で自動生成するため表示件数から除外する
        $total          = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status != 'auto-draft'",
                $post_type,
            )
        );
        $taxonomy_terms = $this->getTaxonomyTerms($post_type);
        $nonce          = wp_create_nonce(self::NONCE_ACTION . '_' . $post_type);
        $ajax_url       = admin_url('admin-ajax.php');
        $ajax_action    = self::AJAX_ACTION;
        $back_url       = admin_url($post_type === 'post' ? 'edit.php' : "edit.php?post_type={$post_type}");

        // WordPress 管理画面のヘッダー・フッターごと出力する
        require_once ABSPATH . 'wp-admin/admin-header.php';
        require get_template_directory() . '/views/app/admin/purge-posts.php';
        require_once ABSPATH . 'wp-admin/admin-footer.php';
    }

    /**
     * Ajax ハンドラ：投稿のバッチ削除、またはタクソノミーのターム全削除を実行する。
     * purge_taxonomy パラメータがある場合はターム削除モードで動作する。
     */
    private function handleAjax(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => '権限がありません'], 403);
        }

        $post_type = sanitize_key($_POST['post_type'] ?? '');
        if (!post_type_exists($post_type)) {
            wp_send_json_error(['message' => '不明な投稿タイプです'], 400);
        }

        check_ajax_referer(self::NONCE_ACTION . '_' . $post_type);

        // タクソノミーのターム削除モード
        $purge_taxonomy = sanitize_key($_POST['purge_taxonomy'] ?? '');
        if ($purge_taxonomy !== '') {
            $this->purgeTerms($post_type, $purge_taxonomy);
            return;
        }

        // 投稿のバッチ削除モード
        global $wpdb;

        wp_defer_term_counting(true);

        $started = microtime(true);
        $deleted = 0;
        $failed  = [];

        do {
            $ids = get_posts([
                'post_type'        => $post_type,
                'post_status'      => ['publish', 'future', 'draft', 'pending', 'private', 'trash', 'auto-draft'],
                'posts_per_page'   => self::BATCH_SIZE,
                'fields'           => 'ids',
                'orderby'          => 'ID',
                'order'            => 'ASC',
                'post__not_in'     => $failed ?: [0],
                'no_found_rows'    => true,
                'suppress_filters' => true,
            ]);

            foreach ($ids as $id) {
                if (wp_delete_post((int) $id, true)) {
                    $deleted++;
                } else {
                    $failed[] = (int) $id;
                }
            }
        } while ($ids && (microtime(true) - $started) < self::BATCH_TIMEOUT);

        wp_defer_term_counting(false);

        $remaining = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status != 'auto-draft'",
                $post_type,
            )
        );

        wp_send_json_success([
            'deleted'   => $deleted,
            'remaining' => $remaining,
            'failed'    => $failed,
        ]);
    }

    /**
     * 指定タクソノミーのターム（カテゴリ・タグ等）を全件削除する。
     */
    private function purgeTerms(string $post_type, string $taxonomy): void
    {
        // 投稿タイプに紐づくタクソノミーのみ受け付ける
        $allowed = get_object_taxonomies($post_type, 'names');
        if (!in_array($taxonomy, $allowed, true)) {
            wp_send_json_error(['message' => '対象外のタクソノミーです'], 400);
        }

        $terms = get_terms([
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'fields'     => 'ids',
            'number'     => 0,
        ]);

        if (is_wp_error($terms)) {
            wp_send_json_error(['message' => $terms->get_error_message()], 500);
        }

        $deleted_count = 0;
        foreach ($terms as $term_id) {
            if (!is_wp_error(wp_delete_term((int) $term_id, $taxonomy))) {
                $deleted_count++;
            }
        }

        wp_send_json_success([
            'taxonomy'      => $taxonomy,
            'terms_deleted' => $deleted_count,
        ]);
    }

    /**
     * Ajax ハンドラ：タクソノミーとターム情報を JSON で返す（非使用・将来拡張用）。
     */
    private function handleGetTerms(): void
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => '権限がありません'], 403);
        }

        $post_type = sanitize_key($_POST['post_type'] ?? '');
        if (!post_type_exists($post_type)) {
            wp_send_json_error(['message' => '不明な投稿タイプです'], 400);
        }

        check_ajax_referer(self::NONCE_ACTION . '_' . $post_type);

        wp_send_json_success($this->getTaxonomyTerms($post_type));
    }
}

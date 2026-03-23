<?php

namespace App\Hooks;

use App\Interfaces\BootableWpHookInterface;
use App\Services\Config;

class PostTaxonomyRequired implements BootableWpHookInterface
{
    private array $config;

    public function __construct()
    {
        $this->config = Config::get('cms.taxonomy_required') ?? [];
    }

    /**
     *
     */
    public function boot(): void
    {
        add_action('save_post',     $this->checkTaxonomy(...));
        add_action('admin_notices', $this->showErrorNotice(...));
    }

    /**
     *
     */
    public function checkTaxonomy(int $post_id): void
    {
        $post_type = get_post_type($post_id);

        if (!isset($this->config[$post_type])) return;

        foreach ($this->config[$post_type] as $taxonomy => $message) {
            if (!empty(wp_get_post_terms($post_id, $taxonomy))) continue;

            remove_action('save_post', $this->checkTaxonomy(...));

            wp_update_post([
                'ID'          => $post_id,
                'post_status' => 'draft',
            ]);

            add_action('save_post', $this->checkTaxonomy(...));

            add_filter('redirect_post_location', function (string $location): string {
                return add_query_arg('taxonomy_error', 1, $location);
            });

            return;
        }
    }

    /**
     *
     */
    public function showErrorNotice(): void
    {
        if (!isset($_GET['taxonomy_error'])) return;

        $post_type = get_current_screen()?->post_type ?? '';
        $message   = $this->errorMessage($post_type);

        if ($message === '') return;

        echo '<div class="notice notice-error"><p>' . esc_html($message) . '</p></div>';
    }

    /**
     *
     */
    private function errorMessage(string $post_type): string
    {
        $post_id = (int) ($_GET['post'] ?? 0);

        foreach ($this->config[$post_type] ?? [] as $taxonomy => $message) {
            if ($post_id && empty(wp_get_post_terms($post_id, $taxonomy))) {
                return $message;
            }
        }

        return '';
    }
}

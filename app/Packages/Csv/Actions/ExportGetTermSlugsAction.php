<?php

namespace App\Packages\Csv\Actions;

/**---------------------------------------------
 *
 * ---------------------------------------------
 */
class ExportGetTermSlugsAction
{
    /**
     *
     */
    public function __invoke(int $post_id, string $taxonomy): string
    {
        if (!taxonomy_exists($taxonomy)) {
            return '';
        }

        $terms = get_the_terms($post_id, $taxonomy);

        if (is_wp_error($terms) || empty($terms)) {
            return '';
        }

        return implode(',', array_map(fn($t) => urldecode($t->slug), $terms));
    }
}

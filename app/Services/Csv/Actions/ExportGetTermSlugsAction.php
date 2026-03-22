<?php

namespace App\Actions\Csv;

/**---------------------------------------------
 * 投稿に紐づくタームスラッグ取得
 * ---------------------------------------------
 * 指定した投稿・タクソノミーに紐づくタームの slug を取得し、
 * CSV出力向けのカンマ区切り文字列で返す invokable クラス。
 */
class ExportGetTermSlugsAction
{
    /**
     * タームスラッグをカンマ区切りで返す
     *
     * - taxonomy が存在しない場合は空文字を返す
     * - タームが取得できない場合や WP_Error の場合も空文字を返す
     * - slug は CSVへ戻しやすいよう urldecode して返す
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

        return \implode(',', \array_map(fn($t) => \urldecode($t->slug), $terms));
    }
}

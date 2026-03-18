<?php

namespace App\UseCase;

use App\CMS\Plugins\Acf;

/**---------------------------------------------
 * ACFのカスタムフィールドを一括取得
 * ---------------------------------------------
 * - 追加編集削除はここだけ触るようにしたい
 * - get_fieldsで取得するのでDBアクセスが1回で済みます
 *
 * 例：
 *  $sample = get_acf( get_the_ID() )->投稿タイプ名();
 *  echo $sample['acf_is_public'];
 */
final class GetAcfFields
{
    public function __construct(
        private readonly int $post_id,
    ) {
    }

    /**
     * 複数のカスタム投稿があり、カスタム投稿それぞれにカスタムフィールドがある場合は、
     * public function news()や、public function works()など、
     * 投稿タイプ名の関数を独自に作成して、関数を投稿タイプごとに切り分けます。
     *
     * その際の呼び出し方は下記となります。
     * $sample = get_acf( get_the_ID() )->関数名();
     * $sample = get_acf( get_the_ID() )->news();
     * $sample = get_acf( get_the_ID() )->works();
     */
    public function news(): array
    {
        return Acf::getByKeys($this->post_id, [
            // 実際のフィールド名に置き換えてください。
            'acf_is_public',
            'acf_price',
        ]);
    }
}

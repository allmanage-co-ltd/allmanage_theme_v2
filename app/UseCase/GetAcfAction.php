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
 *  $sample = get_acf_action( get_the_ID() )->handle();
 *  echo $sample['acf_is_public'];
 */
final class GetAcfAction
{
    public function __construct(
        private readonly int $post_id,
    ) {
        //
    }

    /**
     * サンプルです。
     * 関数名は任意のもの変更して、$keyは実際のフィールド名を入れてください。
     */
    public function handle(): array
    {
        return Acf::getByKeys($this->post_id, [
            // ここに実際のACFフィールド名を追加する
            'acf_is_public',
        ]);
    }
}

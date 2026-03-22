<?php

namespace App\Plugins;

use App\Interfaces\BootableWpHookInterface;

/**---------------------------------------------
 * Advanced Custom Fields 連携クラス
 * ---------------------------------------------
 *
 */
class AcfHook implements BootableWpHookInterface
{
    /**
     * 初期化処理
     */
    public function boot(): void
    {
        if (!\class_exists('ACF') || !\class_exists('acf_pro')) {
            return;
        }
    }
}

<?php

namespace App\Plugins;

use App\Interfaces\BootableWpHookInterface;

/**---------------------------------------------
 * Advanced Custom Fields 連携クラス
 * ---------------------------------------------
 * - Acf専用のフックをまとめる
 */
class AcfHook implements BootableWpHookInterface
{
    public function boot(): void
    {
        if (!\class_exists('ACF') || !\class_exists('acf_pro')) {
            return;
        }
    }
}

<?php

namespace App\WordPress\Hooks;

use App\Errors\AppError;
use App\Interfaces\BootableWpHookInterface;
use App\Support\Config;

/**
 * config/packages.php に登録した起動クラスを順番に boot する。
 *
 * 起動対象を設定ファイルに限定して、どこがテーマ起動に関わるか見通しやすくする。
 */
class Package extends Hook
{
    #[\Override]
    public function boot(): void
    {
        foreach (Config::get('packages.hook_providers', []) as $class) {
            if (!is_subclass_of($class, BootableWpHookInterface::class)) {
                AppError::abort("{$class} は BootableWpHookInterface を実装してください。");
            }

            (new $class())->boot();
        }
    }
}

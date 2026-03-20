<?php

namespace App\CMS\Hooks;

use App\Interfaces\BootableWpHookInterface;
use App\Support\Config;

/**
 * config/packages.php に登録された起動クラスを順に boot する。
 */
class Package extends Hook
{
    #[\Override]
    public function boot(): void
    {
        $classmap = Config::get('packages.hook_providers', []);

        foreach ($classmap as $class) {
            if (!is_subclass_of($class, BootableWpHookInterface::class)) {
                throw new \RuntimeException("{$class} は BootableWpHookInterface を実装していません。");
            }

            (new $class())->boot();
        }
    }
}

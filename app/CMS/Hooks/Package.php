<?php

namespace App\CMS\Hooks;

use App\Packages\BootableInterface;
use App\Support\Config;

/**---------------------------------------------
 * パッケージ起動クラス
 * ---------------------------------------------
 * - config/packages.php の hook_providers に登録されたクラスを順に起動する。
 * - 各クラスは BootableInterface を実装している必要がある
 * - 主な用途は WordPress フックの登録など、起動時に一度だけ必要な初期化処理
 * - クラスの自動探索は行わず、明示的な設定登録を唯一の起点とする
 */
class Package extends Hook
{
    #[\Override]
    public function boot(): void
    {
        $classmap = Config::get('packages.hook_providers');

        foreach ($classmap as $class) {
            if (!is_subclass_of($class, BootableInterface::class)) {
                throw new \RuntimeException("{$class} は BootableInterface を実装していません。");
            }

            (new $class())->boot();
        }
    }
}

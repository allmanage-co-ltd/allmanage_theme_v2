<?php

namespace App\CMS\Hooks;

use App\Packages\BootableInterface;
use App\Support\Config;

/**---------------------------------------------
 * ライブラリ起動クラス
 * ---------------------------------------------
 * - app/Library/ 以下の BootableInterface 実装クラスを自動で boot する
 * - クラス発見は Composer の classmap を使用する（パス依存なし）
 *   → composer dump-autoload により vendor/composer/autoload_classmap.php が生成される
 *   → classmap に登録された全クラスを走査し BootableInterface 実装のみ起動する
 *
 * 新しい機能を追加する場合は app/Library/ 以下に BootableInterface を実装したクラスを置くだけでよい
 * 場所は問わない。WP 依存・フック登録の有無も問わない
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

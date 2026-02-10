<?php

namespace App\Hooks\Plugins;

/**---------------------------------------------
 * Pluginクラス
 * ---------------------------------------------
 *
 * プラグインをごにょるクラスを集約します。
 *
 * プラグイン類は画面にも振るまいにも影響することが多く
 * こいつらだけ特別にディレクトリ切っています。
 *
 * もうここは何してもいいです。
 *
 */
abstract class Plugin
{
    public function __construct()
    {
        //
    }

    abstract public function boot(): void;
}

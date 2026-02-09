<?php

namespace App\Plugins;

/**---------------------------------------------
 * Pluginクラス
 * ---------------------------------------------
 *
 * プラグインをごにょるクラスを集約します。
 *
 * プラグイン類は画面にも振るまいにも影響することが多く
 * HookでもありAdminでもあり...みたいなのがめんどくさいので、
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
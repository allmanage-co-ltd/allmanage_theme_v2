<?php

namespace App\Hooks;

use App\Interfaces\BootableWpHookInterface;

/**---------------------------------------------
 * ショートコード登録クラス
 * ---------------------------------------------
 * - テーマ内で使用するショートコードを登録する
 * - PHP のグローバル関数をそのままショートコード化する
 * - URL / パス系のユーティリティ用途を想定
 */
class AddShortcode implements BootableWpHookInterface
{
  public function boot(): void
  {
    add_shortcode('home', 'home');
    add_shortcode('theme_uri', 'theme_uri');
    add_shortcode('theme_dir', 'theme_dir');
    add_shortcode('img_uri', 'img_uri');
  }
}

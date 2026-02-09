<?php

namespace App\Hooks;

/**---------------------------------------------
 * ショートコード登録クラス
 * ---------------------------------------------
 * - テーマ内で使用するショートコードを登録する
 * - PHP のグローバル関数をそのままショートコード化する
 * - URL / パス系のユーティリティ用途を想定
 */
class Shortcode extends Hook
{
  public function __construct()
  {
    //
  }

  /**
   * フック登録
   */
  public function boot(): void
  {
    add_shortcode('home', 'home');
    add_shortcode('theme_uri', 'theme_uri');
    add_shortcode('theme_dir', 'theme_dir');
    add_shortcode('img_dir', 'img_dir');
  }
}
<?php

namespace App\Plugins;

/**---------------------------------------------
 * Advanced Custom Fields 連携クラス
 * ---------------------------------------------
 *
 */
class AdvancedCustomFields extends Plugin
{
  public function __construct()
  {
    if (! class_exists('ACF') || ! class_exists('acf_pro'))
      return;
  }

  /**
   * 初期化処理
   */
  public function boot(): void
  {
    // add_action('acf/init', [$this, 'registerOptionPage']);
  }

  /**
   * オプションページ登録
   *
   * - 必要な時だけ使用する
   */
  public function registerOptionPage(): void
  {
    // if (function_exists('acf_add_options_page')) {
    //     acf_add_options_page([
    //         'page_title' => 'テーマ設定',
    //         'menu_title' => 'テーマ設定',
    //         'menu_slug'  => 'theme-settings',
    //         'capability' => 'manage_options',
    //         'redirect'   => false,
    //     ]);
    // }
  }
}
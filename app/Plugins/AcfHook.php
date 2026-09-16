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

    add_filter('site_transient_update_plugins', $this->hiddenUpdateFlug(...));
  }

  /**
   * ACFPROのバージョンアップフラグを非表示
   */
  public function hiddenUpdateFlug(mixed $transient)
  {
    $plugin = 'advanced-custom-fields-pro/acf.php';

    if (isset($transient->response[$plugin])) {
      $transient->no_update[$plugin] = $transient->response[$plugin];
      unset($transient->response[$plugin]);
    }

    return $transient;
  }
}

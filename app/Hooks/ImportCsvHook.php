<?php

namespace App\Hooks;

use App\Error\AppError;
use App\Interfaces\BootableWpHookInterface;
use App\Services\Csv\ImportCsvAbstract;
use App\Services\Config;

/**---------------------------------------------
 * CSV インポートの入口。
 * ---------------------------------------------
 * 設定に登録された importer を ImportCsvAbstract のサブクラスとして検証する。
 */
class ImportCsvHook implements BootableWpHookInterface
{
  public function boot(): void
  {
    add_action('init', $this->register(...), 20);
  }

  private function register(): void
  {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
      return;
    }

    foreach (Config::get('cms.option_pages.csv-in-expoter.importer', []) as $class) {
      if (!\is_string($class) || !\is_subclass_of($class, ImportCsvAbstract::class)) {
        continue;
      }

      $param = $class::importParam();

      if (!isset($_POST[$param]) || $_POST[$param] !== $class::postType()) {
        continue;
      }

      // パラメータが一致したがアクセス権限がない場合はエラーで停止する
      if (!$class::isAllowed()) {
        AppError::abort(new \RuntimeException('CSVインポートの権限がありません'));
      }

      /** @var ImportCsvAbstract $importer */
      $importer = new $class();
      $importer->handle();

      $redirectUrl = add_query_arg($class::successParam(), 1, $class::redirectUrl());
      wp_redirect($redirectUrl);
      exit;
    }
  }
}

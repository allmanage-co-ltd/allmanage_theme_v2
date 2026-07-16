<?php

namespace App\Hooks;

use App\Error\AppError;
use App\Interfaces\BootableWpHookInterface;
use App\Services\Csv\ExportCsvAbstract;
use App\Services\Config;

/**---------------------------------------------
 * CSV エクスポートの入口。
 * ---------------------------------------------
 * 設定に登録された exporter を ExportCsvAbstract のサブクラスとして検証する。
 */
class ExportCsvHook implements BootableWpHookInterface
{
  public function boot(): void
  {
    add_action('init', $this->register(...), 20);
  }

  private function register(): void
  {
    foreach (Config::get('cms.option_pages.csv-in-exporter.exporter', []) as $class) {
      if (!\is_string($class) || !\is_subclass_of($class, ExportCsvAbstract::class)) {
        continue;
      }

      $param = $class::exportParam();

      if (!isset($_GET[$param]) || $_GET[$param] !== $class::postType()) {
        continue;
      }

      // パラメータが一致したがアクセス権限がない場合はエラーで停止する
      if (!$class::isAllowed()) {
        AppError::abort(new \RuntimeException('CSVエクスポートの権限がありません'));
      }

      /** @var ExportCsvAbstract $exporter */
      $exporter = new $class();
      $exporter->handle();
      return;
    }
  }
}

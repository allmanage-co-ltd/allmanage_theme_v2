<?php

namespace App\Hooks;

use App\Interfaces\BootableWpHookInterface;
use App\Interfaces\CsvExporterInterface;
use App\Services\Config;

/**
 * CSV エクスポートの入口。
 *
 * 設定に登録された exporter を CsvExporterInterface で扱う。
 */
class ExportCsvHook implements BootableWpHookInterface
{
    public function boot(): void
    {
        add_action('init', $this->register(...));
    }

    private function register(): void
    {
        foreach (Config::get('cms.option_pages.csv-in-expoter.exporter', []) as $class) {
            if (!\is_string($class) || !\is_subclass_of($class, CsvExporterInterface::class)) {
                continue;
            }

            $param = $class::exportParam();

            if (!isset($_GET[$param]) || $_GET[$param] !== $class::postType()) {
                continue;
            }

            if (!$class::isAllowed()) {
                continue;
            }

            /** @var CsvExporterInterface $exporter */
            $exporter = new $class();
            $exporter->handle();
            return;
        }
    }
}

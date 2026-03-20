<?php

namespace App\WordPress\Csv\Hooks;

use App\Interfaces\BootableWpHookInterface;
use App\Support\Config;
use App\WordPress\Csv\Abstracts\ExportCsv;

/**
 * CSV エクスポートの入口。
 *
 * 設定に登録された exporter の中から、クエリに一致するものだけを実行する。
 */
class ExportCsvHook implements BootableWpHookInterface
{
    public function boot(): void
    {
        add_action('init', $this->register(...));
    }

    private function register(): void
    {
        foreach (Config::get('packages.csv.exporter', []) as $class) {
            if (!is_subclass_of($class, ExportCsv::class)) {
                continue;
            }

            $param = $class::exportParam();

            if (!isset($_GET[$param])) {
                continue;
            }

            if ($_GET[$param] !== $class::postType()) {
                continue;
            }

            if (!$class::isAllowed()) {
                continue;
            }

            (new $class())->handle();
            return;
        }
    }
}

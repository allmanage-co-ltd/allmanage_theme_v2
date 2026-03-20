<?php

namespace App\Packages\Csv\Hooks;

use App\Packages\BootableInterface;
use App\Packages\Csv\Abstracts\ExportCsv;
use App\Support\Config;

class ExportCsvHook implements BootableInterface
{
    public function boot(): void
    {
        add_action('init', $this->register(...));
    }

    /**
     * CSVエクスポート処理のルーティング
     *
     * - exportParam() のクエリパラメータが存在し、値が postType() と一致するクラスの handle() を実行する
     */
    private function register(): void
    {
        foreach (Config::get('packages.csv.exporter') as $class) {
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

            $exporter = new $class();
            $exporter->handle();

            return;
        }
    }
}

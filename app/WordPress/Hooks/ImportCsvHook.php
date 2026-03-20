<?php

namespace App\Packages\Csv\Hooks;

use App\Errors\AppError;
use App\Interfaces\BootableWpHookInterface;
use App\Packages\Csv\Abstracts\ImportCsv;
use App\Support\Config;

class ImportCsvHook implements BootableWpHookInterface
{
    public function boot(): void
    {
        add_action('init', $this->register(...));
    }

    /**
     * CSVインポート処理のルーティング。
     */
    private function register(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return;
        }

        foreach (Config::get('packages.csv.importer', []) as $class) {
            if (!is_subclass_of($class, ImportCsv::class)) {
                continue;
            }

            $param = $class::importParam();

            if (!isset($_POST[$param])) {
                continue;
            }

            if ($_POST[$param] !== $class::postType()) {
                continue;
            }

            if (!$class::isAllowed()) {
                continue;
            }

            $importer = new $class();

            try {
                $importer->handle();
            } catch (\Throwable $throwable) {
                AppError::fromThrowable($throwable, [
                    'package' => 'csv-import',
                    'post_type' => $class::postType(),
                ]);
            }

            $redirectUrl = add_query_arg($class::successParam(), 1, $importer->redirectUrl());
            wp_redirect($redirectUrl);
            exit;
        }
    }
}

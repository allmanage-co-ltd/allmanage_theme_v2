<?php

namespace App\Hooks;

use App\Errors\AppError;
use App\Interfaces\BootableWpHookInterface;
use App\Interfaces\CsvImporterInterface;
use App\Support\Config;

/**
 * CSV インポートの入口。
 *
 * 設定に登録された importer を CsvImporterInterface で扱う。
 */
class ImportCsvHook implements BootableWpHookInterface
{
    public function boot(): void
    {
        add_action('init', $this->register(...));
    }

    private function register(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return;
        }

        foreach (Config::get('cms.option_pages.csv-in-expoter.importer', []) as $class) {
            if (!\is_string($class) || !\is_subclass_of($class, CsvImporterInterface::class)) {
                continue;
            }

            $param = $class::importParam();

            if (!isset($_POST[$param]) || $_POST[$param] !== $class::postType()) {
                continue;
            }

            if (!$class::isAllowed()) {
                continue;
            }

            /** @var CsvImporterInterface $importer */
            $importer = new $class();

            try {
                $importer->handle();
            } catch (\Throwable $throwable) {
                AppError::abort($throwable);
            }

            $redirectUrl = add_query_arg($class::successParam(), 1, $class::redirectUrl());
            wp_redirect($redirectUrl);
            exit;
        }
    }
}

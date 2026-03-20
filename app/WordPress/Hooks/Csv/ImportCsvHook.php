<?php

namespace App\WordPress\Csv\Hooks;

use App\Errors\AppError;
use App\Interfaces\BootableWpHookInterface;
use App\Shared\Config;
use App\WordPress\Csv\Abstracts\ImportCsvAbstract;

/**
 * CSV インポートの入口。
 *
 * 実処理の例外はここでまとめて受け、画面終了は AppError::abort() に寄せる。
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

        foreach (Config::get('csv.importer', []) as $class) {
            if (!is_subclass_of($class, ImportCsvAbstract::class)) {
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
                AppError::abort($throwable->getMessage());
            }

            $redirectUrl = add_query_arg($class::successParam(), 1, $importer->redirectUrl());
            wp_redirect($redirectUrl);
            exit;
        }
    }
}

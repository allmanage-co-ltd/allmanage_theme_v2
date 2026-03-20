<?php

namespace App\Packages\Csv\Hooks;

use App\Packages\BootableInterface;
use App\Packages\Csv\Abstracts\ImportCsv;
use App\Support\Config;

class ImportCsvHook implements BootableInterface
{
    public function boot(): void
    {
        add_action('init', $this->register(...));
    }

    /**
     * CSVインポート処理のルーティング
     *
     * - importParam() のパラメータが存在し、値が postType() と一致するクラスの handle() を実行する
     * - 実行後は redirectUrl() 指定のページへリダイレクトし、successParam() をクエリパラメータに追加
     */
    private function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        foreach (Config::get('packages.csv.importer') as $class) {
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
            $importer->handle();
            
            $redirect_url = $importer->redirectUrl();
            $redirect_url = add_query_arg($class::successParam(), 1, $redirect_url);
            wp_redirect($redirect_url);

            exit;
        }
    }
}

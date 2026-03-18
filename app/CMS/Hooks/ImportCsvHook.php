<?php

namespace App\CMS\Hooks;

use App\Support\Config;

class ImportCsvHook extends Hook
{
    /**
     * フック登録
     */
    #[\Override]
    public function boot(): void
    {
        add_action('init', $this->register(...));
    }

    /**
     * CSVインポート処理のルーティング
     *
     * - ?csv_import=xxx のクエリパラメータを確認
     * - config/csv.php に定義されたインポーターを順に評価
     * - filename() と一致するクラスの handle() を実行する
     */
    public function register(): void
    {
        if (!isset($_REQUEST['csv_import'])) {
            return;
        }

        $handled = false;

        foreach (Config::get('csv.importer') as $class) {

            $importer = new $class();

            if ($_REQUEST['csv_import'] === $importer->key()) {
                if (!$importer->can()) {
                    continue;
                }
                $importer->handle();

                $redirect_url = admin_url('admin.php?page=' . Config::get('cms.option_pages.csv-in-expoter.slug'));
                $redirect_url = add_query_arg('import_done', '1', $redirect_url);
                wp_redirect($redirect_url);
                exit;
            }
        }

        if (!$handled) {
            wp_die('エラー: ' . esc_html($_REQUEST['csv_import']) . ' インポートツールが無効か、または存在しません。');
        }
    }
}

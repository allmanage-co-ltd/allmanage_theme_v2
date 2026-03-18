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
     * - ?import=xxx のクエリパラメータを確認
     * - config/csv.php に定義されたインポーターを順に評価
     * - filename() と一致するクラスの handle() を実行する
     */
    public function register(): void
    {
        if (!isset($_GET['import'])) {
            return;
        }

        foreach (Config::get('csv.importer') as $class) {

            $importer = new $class();

            if ($_GET['import'] === $importer->key()) {
                $importer->handle();
            }
        }
    }
}

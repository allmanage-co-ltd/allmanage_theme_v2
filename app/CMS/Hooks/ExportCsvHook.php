<?php

namespace App\CMS\Hooks;

use App\Support\Config;

class ExportCsvHook extends Hook
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
     * CSVエクスポート処理のルーティング
     *
     * - ?export=xxx のクエリパラメータを確認
     * - config/csv.php に定義されたエクスポーターを順に評価
     * - filename() と一致するクラスの handle() を実行する
     */
    public function register(): void
    {
        if (!isset($_GET['export'])) {
            return;
        }

        foreach (Config::get('csv.exporter') as $class) {

            $exporter = new $class();

            if ($_GET['export'] === $exporter->key()) {
                $exporter->handle();
            }
        }
    }
}

<?php

use App\WordPress\Hooks\Enqueue;
use App\WordPress\Hooks\SetupTheme;
use App\WordPress\Hooks\Shortcode;
use App\WordPress\Hooks\Seo;
use App\WordPress\Admin\EditMenuAdmin;
use App\WordPress\Admin\EditMenuClient;
use App\WordPress\Admin\RegisterOptionPage;
use App\WordPress\Admin\RegisterPostType;
use App\WordPress\Admin\RegisterTaxonomy;
use App\WordPress\Plugins\Acf\Acf;
use App\WordPress\Plugins\MwForm\MwForm;
use App\WordPress\Plugins\Welcart\Welcart;
use App\Project\RequestAccessLog;
use App\Project\EditNewsPostColumns;
use App\WordPress\Csv\Hooks\ExportCsvHook;
use App\WordPress\Csv\Hooks\ImportCsvHook;

/**---------------------------------------------
 * アプリケーション起動クラス
 * ---------------------------------------------
 * - テーマ内のフック関連クラスを起動する
 * - WordPressの実行に必要な初期処理を束ねる
 * - 処理は書かない
 * - 登録と起動のみを行う
 * - 依存関係はここで一元管理する
 */
class App
{
    /**
     * 各 WordPress 起動クラスを初期化
     */
    public function boot(): void
    {
        (new SetupTheme())->boot();
        (new Shortcode())->boot();
        (new Enqueue())->boot();
        (new Seo())->boot();
        (new RegisterPostType())->boot();
        (new RegisterTaxonomy())->boot();
        (new RegisterOptionPage)->boot();
        (new EditMenuAdmin())->boot();
        (new EditMenuClient())->boot();
        (new Acf())->boot();
        (new MwForm())->boot();
        (new Welcart())->boot();

        (new ExportCsvHook())->boot();
        (new ImportCsvHook())->boot();

        (new RequestAccessLog())->boot();
        (new EditNewsPostColumns())->boot();
    }
}

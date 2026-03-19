<?php

use App\CMS\Hooks\Enqueue;
use App\CMS\Hooks\Package;
use App\CMS\Hooks\SetupTheme;
use App\CMS\Hooks\Shortcode;
use App\CMS\Hooks\Seo;
use App\CMS\Admin\EditMenuAdmin;
use App\CMS\Admin\EditMenuClient;
use App\CMS\Admin\RegisterOptionPage;
use App\CMS\Admin\RegisterPostType;
use App\CMS\Admin\RegisterTaxonomy;
use App\CMS\Plugins\Acf;
use App\CMS\Plugins\MwWpForm;
use App\CMS\Plugins\Welcart;
use App\UseCase\RequestAccessLog;
use App\UseCase\EditNewsPostColumns;

// use App\CMS\Plugins\WpMembers;

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
     * 各CMSフッククラスを初期化
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
        (new MwWpForm())->boot();
        (new Welcart())->boot();
        // (new WpMembers())->boot();

        (new Package())->boot();

        (new RequestAccessLog())->boot();
        (new EditNewsPostColumns())->boot();
    }
}

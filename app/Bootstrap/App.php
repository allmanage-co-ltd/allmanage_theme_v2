<?php

namespace App\Bootstrap;

use App\Hooks\Enqueue;
use App\Hooks\SetupTheme;
use App\Hooks\Shortcode;
use App\Hooks\Seo;
use App\Admin\EditMenuAdmin;
use App\Admin\EditMenuClient;
use App\Admin\RegisterOptionPage;
use App\Admin\RegisterPostType;
use App\Admin\RegisterTaxonomy;
use App\Plugins\AdvancedCustomFields;
use App\Plugins\MwWpForm;
use App\Plugins\Welcart;

/**---------------------------------------------
 * アプリケーション起動クラス
 * ---------------------------------------------
 *
 * 役割：
 * - テーマ内のフック関連クラスを起動する
 * - WordPressの実行に必要な初期処理を束ねる
 *
 * 方針：
 * - 処理は書かない
 * - 登録と起動のみを行う
 * - 依存関係はここで一元管理する
 *
 * 位置づけ：
 * - テーマのエントリーポイント
 */
class App
{
  public function __construct()
  {
    //
  }

  /**
   *
   */
  public function boot(): void
  {
    //
    (new SetupTheme())->boot();
    (new Shortcode())->boot();
    (new Enqueue())->boot();
    (new Seo())->boot();

    //
    (new RegisterPostType())->boot();
    (new RegisterTaxonomy())->boot();
    (new RegisterOptionPage)->boot();
    (new EditMenuAdmin())->boot();
    (new EditMenuClient())->boot();

    //
    (new AdvancedCustomFields())->boot();
    (new MwWpForm())->boot();
    (new Welcart())->boot();
  }
}
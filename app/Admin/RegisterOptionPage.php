<?php

namespace App\Admin;

use App\Services\Config;

/**---------------------------------------------
 * 管理画面オプションページ登録クラス
 * ---------------------------------------------
 * - 管理画面側で使用する Admin クラス
 * - CSV 入出力用の管理ページを追加する
 * - 表示可否は Config 設定により制御する
 * - add_menu_page を散らさない
 * - functions.php に管理画面ロジックを書かない
 * - 管理画面の表示有無を設定ファイル主導で切り替える
 * - View とロジックを分離する
 */
class RegisterOptionPage extends Admin
{
  public function __construct()
  {
    //
  }

  /**
   * 初期化処理
   */
  public function boot(): void
  {
    if (Config::get('csv.show_admin_menu')) {
      add_action('admin_menu', [$this, 'csvInExpoterRegister']);
    }
  }

  /**
   * CSV 管理画面メニュー登録
   */
  public function csvInExpoterRegister(): void
  {
    add_menu_page(
      'CSV',
      'CSV',
      'manage_options',
      'csv-in-expoter',
      [$this, 'csvInExpoterView']
    );
  }

  /**
   * CSV 管理画面表示
   *
   * - 管理画面用 View ファイルを読み込む
   */
  public function csvInExpoterView(): void
  {
    include theme_dir() . '/views/admin/csv-in-expoter.php';
  }
}
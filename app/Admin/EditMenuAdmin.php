<?php

namespace App\Admin;

/**---------------------------------------------
 * 管理者向け管理画面表示制御クラス
 * ---------------------------------------------
 * - administrator ロールのみを対象に処理を行う
 * - 管理バーの表示を制御する
 * - 表示制御ロジックを functions.php に書かない
 */
class EditMenuAdmin extends Admin
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
    if (!$this->subjectRoles()) {
      return;
    }

    $this->hiddenAdminBar();
  }

  /**
   * 対象ユーザー判定
   */
  public function subjectRoles(): bool
  {
    return current_user_can('administrator');
  }

  /**
   * 管理バー非表示処理
   */
  public function hiddenAdminBar(): void
  {
    show_admin_bar(false);
  }
}
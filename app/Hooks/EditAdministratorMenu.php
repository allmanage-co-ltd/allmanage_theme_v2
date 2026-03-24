<?php

namespace App\Hooks;

use App\Interfaces\BootableWpHookInterface;

/**---------------------------------------------
 * 管理者向け管理画面表示制御クラス
 * ---------------------------------------------
 * - administrator ロールのみを対象に処理を行う
 * - 管理バーの表示を制御する
 * - 表示制御ロジックを functions.php に書かない
 */
class EditAdministratorMenu implements BootableWpHookInterface
{
    public function boot(): void
    {
        if (!$this->role()) {
            return;
        }

        $this->hiddenAdminBar();
    }

    /**
     * 対象ユーザー判定
     */
    public function role(): bool
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

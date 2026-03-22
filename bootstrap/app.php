<?php

use App\Hooks\Core\HooksAutoLoader;

/**---------------------------------------------
 * アプリケーション起動クラス
 * ---------------------------------------------
 * - テーマ内の起動処理をまとめる入口
 * - WordPressの実行に必要な初期処理を束ねる
 * - 処理は書かない
 * - 登録と起動のみを行う
 *
 * app/ 配下でBootableWpHookInterface を実装したクラスが
 * HooksAutoLoader::handle() により自動 Boot される
 */
class App
{
    /**
     * 各 WordPress 起動クラスを初期化
     */
    public function boot(): void
    {
        HooksAutoLoader::handle();
    }
}

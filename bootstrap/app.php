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
 * 今回のアップデートで、app/ 配下のクラスが
 * BootableWpHookInterface を実装していれば
 * HooksAutoLoader により自動 Boot される仕様に変更した。
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

<?php

use App\Actions\Hook\HooksAutoLoader;

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
        (new HooksAutoLoader)->handle();
    }
}

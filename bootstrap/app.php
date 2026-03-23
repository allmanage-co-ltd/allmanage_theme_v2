<?php

use App\Error\AppError;
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
     *
     * - HooksAutoLoader::handle() がスキャン・キャッシュ処理ごと失敗した場合は
     *   AppError::abort() で安全に停止する
     * - 個別 Hook クラスの boot() 内で起きた例外は HooksAutoLoader 側で吸収される
     */
    public function boot(): void
    {
        try {
            HooksAutoLoader::handle();
        } catch (\Throwable $throwable) {
            AppError::abort($throwable);
        }
    }
}

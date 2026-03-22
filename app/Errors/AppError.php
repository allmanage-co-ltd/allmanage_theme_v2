<?php

namespace App\Errors;

use App\Actions\Logger\ResolveLogFieldAction;
use App\Interfaces\AbortAppErrorInterface;
use App\Support\Config;
use App\Support\Logger;
use App\Support\Runtime;

/**
 * テーマ内で処理を止めたい時の共通窓口
 *
 * - すべてのエラー終了処理をここに集約する
 * - wp_die / exit を直接呼ばないためのラッパー
 */
final class AppError implements AbortAppErrorInterface
{
    /**
     * エラーを受け取り処理を停止する
     */
    public static function abort(\Throwable $throwable): never
    {
        if (Runtime::isLocal()) {
            self::exiter($throwable->__toString());
        }

        $error_id = Runtime::errorId();

        // ユーザー向けメッセージ（内部情報は出さない）
        $client_to_error = \nl2br("
        処理中にエラーが発生しました。
        管理者へエラーコードをお知らせください。
        エラーコード: {$error_id}
        ");

        // エラーログを記録する
        // クライアントから提供されるエラーコードをキーに検索できるように
        Logger::error()->error($throwable->getMessage(), [
            'error_id' => $error_id,
            'trace'    => $throwable->getTraceAsString(),
            ...ResolveLogFieldAction::handle(Config::get('logger.error.content')),
        ]);

        self::exiter($client_to_error);
    }

    /**
     * 実際に処理を終了する
     *
     * - WordPress環境では wp_die を使用
     * - それ以外は exit を使用
     */
    public static function exiter(string $message): never
    {
        if (\function_exists('wp_die')) {
            \wp_die($message, '', ['response' => 500]);;
        }

        \exit($message);
    }
}

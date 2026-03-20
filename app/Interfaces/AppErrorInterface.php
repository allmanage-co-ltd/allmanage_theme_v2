<?php

namespace App\Interfaces;

/**
 * テーマ全体の終了系エラー窓口。
 *
 * 実際の終了方法はクラス側で持ち、呼び出し側は abort() だけを使う。
 */
interface AppErrorInterface
{
    public static function abort(string $message): never;
}

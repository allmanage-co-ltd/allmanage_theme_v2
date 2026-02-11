<?php

namespace App\Services;

/**---------------------------------------------
 * 実行環境判定サービス
 * ---------------------------------------------
 * - 実行中の環境やアクセス元を判定するための共通サービス
 * - ローカル判定、モバイル判定、Bot 判定をまとめて提供する
 * - テンプレートや各所で $_SERVER を直接触らせないための窓口
 */
class Runtime extends Service
{
    /**
     * local環境判定
     */
    public static function isLocal(): bool
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';

        if ($host === '') {
            return false;
        }

        $locals = Config::get('app.runtime.local');

        foreach ($locals as $local) {
            if (strpos($host, $local) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * モバイル判定
     */
    public static function isMobile(): bool
    {
        return self::matchAgent(Config::get('app.runtime.mobile'));
    }

    /**
     * Bot判定
     */
    public static function isBot(): bool
    {
        return self::matchAgent(Config::get('app.runtime.robots'));
    }

    /**
     * UAマッチ共通処理
     */
    private static function matchAgent(array $keywords): bool
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if ($ua === '') {
            return false;
        }

        foreach ($keywords as $word) {
            if (stripos($ua, $word) !== false) {
                return true;
            }
        }

        return false;
    }
}

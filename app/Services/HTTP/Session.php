<?php

namespace App\Services\HTTP;

/**---------------------------------------------
 * セッション管理サービス
 * ---------------------------------------------
 * - PHP標準の $_SESSION を直接触らせないためのラッパー
 * - セッション開始処理と設定を一箇所に集約する
 * - View / Controller / Service から共通APIで扱えるようにする
 */
class Session
{
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            self::start();
        }
    }

    /**
     * セッション開始処理
     *
     * - 有効期限を30日に設定
     * - Cookieのセキュリティ設定をここで統一
     * - セッション名を独自名に変更
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        if (headers_sent()) {
            return;
        }

        ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
        ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30);
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.use_strict_mode', '1');
        session_name('APPSESSID');
        session_start();
    }

    /**
     * セッション取得
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return $_SESSION[$name] ?? $default;
    }

    /**
     * セッション保存
     *
     * - 型は制限しない（配列・文字列・数値すべて可）
     */
    public function set(string $name, mixed $data): void
    {
        $_SESSION[$name] = $data;
    }

    /**
     * セッション削除（単一キー）
     *
     * - ログイン情報やフラッシュ削除用
     */
    public function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * セッションIDを再生成する
     *
     * ログイン直後などにセッション固定化対策として使用する。
     */
    public function regenerateId(bool $deleteOld_session = true): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            self::start();
        }

        session_regenerate_id($deleteOld_session);
    }

    /**
     * セッションを完全破棄する
     */
    public function reset(): void
    {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * フラッシュ値を保存する（1リクエスト限定）
     */
    public function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * フラッシュ値を取り出して同時に削除する
     */
    public function pull(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);

        return $value;
    }

    /**
     * - デバッグ用
     */
    public function debug(): void
    {
        d($_SESSION);
    }
}

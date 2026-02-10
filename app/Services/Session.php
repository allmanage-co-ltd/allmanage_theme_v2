<?php

namespace App\Services;

/**---------------------------------------------
 * セッション管理サービス
 * ---------------------------------------------
 * - PHP標準の $_SESSION を直接触らせないためのラッパー
 * - セッション開始処理と設定を一箇所に集約する
 * - View / Controller / Service から共通APIで扱えるようにする
 */
class Session extends Service
{
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            self::start();
        }
    }

    /**
     * インスタンス生成
     *
     * - 明示的に new したい場合のためのファクトリ
     */
    public static function new(): self
    {
        return new self();
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
     * - デバッグ用
     */
    public function debug(): void
    {
        d($_SESSION);
    }
}
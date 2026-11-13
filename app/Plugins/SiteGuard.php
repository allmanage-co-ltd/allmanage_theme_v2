<?php

declare(strict_types=1);

namespace App\Plugins;

/**---------------------------------------------
 * SiteGuard WP Plugin 連携クラス
 * ---------------------------------------------
 * - プラグインの存在確認・設定値の取得・更新を提供する
 * - デフォルト設定値はこのクラスで一元管理する
 */
class SiteGuard
{
    private const OPTION_KEY = 'siteguard_config';

    /**
     * SiteGuard がインストール済みかつ有効か確認する
     */
    public static function isActive(): bool
    {
        return class_exists('SiteGuard_Config');
    }

    /**
     * 現在の設定値を取得する
     *
     * @return array<string, mixed>
     */
    public static function getConfig(): array
    {
        $config = get_option(self::OPTION_KEY, []);
        return is_array($config) ? $config : [];
    }

    /**
     * 設定値を部分的に上書きする（指定キーのみ更新）
     *
     * @param array<string, mixed> $values
     */
    public static function updateConfig(array $values): void
    {
        $config = self::getConfig();
        update_option(self::OPTION_KEY, array_merge($config, $values));
    }

    /**
     * テーマで管理するデフォルト設定。
     * SiteGuard インストール直後に適用する値をここで定義する。
     *
     * @return array<string, mixed>
     */
    public static function defaultConfig(): array
    {
        return [
            // ログインページURLの変更を無効化（Xserver では .htaccess の書き換えが不要なため）
            'renamelogin_enable'      => '0',
            // ログイン失敗ロック：有効・5分間で3回失敗で60秒ロック
            'loginlock_enable'        => '1',
            'loginlock_interval'      => '5',
            'loginlock_threshold'     => '3',
            'loginlock_locksec'       => '60',
            // 画像認証：ログイン・コメント・パスワードリセット・ユーザー登録で有効
            'captcha_enable'          => '1',
            'captcha_login'           => '1',
            'captcha_comment'         => '1',
            'captcha_lostpasswd'      => '1',
            'captcha_registuser'      => '1',
            // ログインエラーを統一メッセージにする
            'same_login_error'        => '1',
            // ピンバック無効（SetupSecurity と二重で守る）
            'disable_pingback_enable' => '1',
            // author クエリのブロック
            'block_author_query_enable' => '1',
            // XML-RPC はテーマ側の SetupSecurity で制御するためプラグインでは無効
            'disable_xmlrpc_enable'   => '0',
            // ログイン通知はメール設定が不定のため無効
            'loginalert_enable'       => '0',
            // アップデート通知はすべて無効
            'notify_wpcore'           => '0',
            'notify_plugins'          => '0',
            'notify_themes'           => '0',
            'updates_notify_enable'   => '0',
            // 管理者通知バナーを表示
            'show_admin_notices'      => '1',
        ];
    }
}

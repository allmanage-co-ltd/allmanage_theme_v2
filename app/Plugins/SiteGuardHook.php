<?php

declare(strict_types=1);

namespace App\Plugins;

use App\Interfaces\BootableWpHookInterface;

/**---------------------------------------------
 * SiteGuard WP Plugin フッククラス
 * ---------------------------------------------
 * - プラグイン有効化時にデフォルト設定を自動注入する
 * - SiteGuard が未インストールの場合は何もしない
 */
class SiteGuardHook implements BootableWpHookInterface
{
    public function boot(): void
    {
        if (!SiteGuard::isActive()) {
            return;
        }

        // SiteGuard プラグイン有効化のタイミングでデフォルト設定を注入する。
        // plugin_action_links_ は有効化後の最初のリクエストで発火するため、
        // 設定が未注入の場合のみ上書きする。
        add_action('admin_init', $this->applyDefaults(...));
    }

    /**
     * SiteGuard のデフォルト設定を適用する。
     * SiteGuard 自身がプラグイン有効化時に初期値を書き込むため、空チェックは使わない。
     * テーマ側の注入済みフラグ（theme_defaults_applied）が立っていれば再注入しない。
     * これにより「プラグイン再有効化のたびに上書き」は防ぎつつ、
     * 管理画面での手動設定変更も尊重する（フラグをリセットすれば再注入できる）。
     */
    private function applyDefaults(): void
    {
        $config = SiteGuard::getConfig();

        // テーマ側でデフォルト注入済みなら何もしない
        if (!empty($config['theme_defaults_applied'])) {
            return;
        }

        // デフォルト設定に注入済みフラグを付けて保存する
        SiteGuard::updateConfig(array_merge(
            SiteGuard::defaultConfig(),
            ['theme_defaults_applied' => '1'],
        ));
    }
}

<?php

namespace App\Hooks;

use App\Interfaces\BootableWpHookInterface;
use App\Services\Config;

/**---------------------------------------------
 * 管理画面メニュー制御クラス（クライアント向け）
 * ---------------------------------------------
 * - 管理画面の表示メニューを権限別に制御する
 * - editor 以下のユーザーを対象に UI を簡略化する
 * - 制御内容は Config 設定により切り替える
 * - functions.php に直接書かない
 */
class EditClientMenu implements BootableWpHookInterface
{
    private $visible_custom_menus;
    private $opts;

    public function __construct()
    {
        $config                     = Config::get('menu.client_menu');
        $this->visible_custom_menus = $config['visible'];
        $this->opts                 = $config['default_option'];
    }

    /**
     * 初期化処理
     */
    public function boot(): void
    {
        if (!$this->role()) {
            return;
        }

        add_action('admin_menu', $this->removeMenusForEditor(...), 9999);
        add_action('admin_init', $this->hideUpdateNoticeForEditor(...));
        add_action('admin_bar_menu', $this->customizeAdminBarForLimitedUsers(...), 9999);
    }

    /**
     * 対象ユーザー判定
     */
    public function role(): bool
    {
        if (current_user_can('administrator')) {
            return false;
        }

        return current_user_can('editor')
            || current_user_can('author')
            || current_user_can('contributor')
            || current_user_can('subscriber');
    }

    /**
     * 管理画面メニューの制御
     */
    public function removeMenusForEditor()
    {
        // 表示するメニューを組み立てる
        $keep_menus = [];

        if (!empty($this->visible_custom_menus['post_type'])) {
            foreach ($this->visible_custom_menus['post_type'] as $post_type) {
                $keep_menus[] = 'edit.php?post_type=' . $post_type;
            }
        }

        if (!empty($this->visible_custom_menus['option'])) {
            foreach ($this->visible_custom_menus['option'] as $option_page) {
                $keep_menus[] = $option_page;
            }
        }

        global $menu;

        foreach ($menu as $value) {
            $menu_slug = $value[2];
            $keep      = \in_array($menu_slug, $keep_menus);

            if (!$keep && !empty($GLOBALS['submenu'][$menu_slug])) {
                foreach ($GLOBALS['submenu'][$menu_slug] as $submenu_item) {
                    if (\in_array($submenu_item[2], $keep_menus)) {
                        $keep = true;
                        break;
                    }
                }
            }

            if (!$keep) {
                remove_menu_page($menu_slug);
            }
        }

        if (!$this->opts['helth']) {
            remove_meta_box('dashboard_site_health', 'dashboard', 'normal');
        }
        if (!$this->opts['activity']) {
            remove_meta_box('dashboard_activity', 'dashboard', 'normal');
        }
        if (!$this->opts['quick_press']) {
            remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
        }
        if (!$this->opts['primary']) {
            remove_meta_box('dashboard_primary', 'dashboard', 'side');
        }
        if (!$this->opts['panel']) {
            remove_action('welcome_panel', 'wp_welcome_panel');
        }
        if (!$this->opts['right_now']) {
            remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
        }
    }

    /**
     * 更新通知の制御
     */
    public function hideUpdateNoticeForEditor()
    {
        if (!$this->opts['notices']) {
            remove_action('admin_notices', 'update_nag', 3);
            remove_action('network_admin_notices', 'update_nag', 3);
        }
    }

    /**
     * 管理バーの表示制御
     */
    public function customizeAdminBarForLimitedUsers($wp_admin_bar)
    {
        if (!$this->opts['new-content']) {
            $wp_admin_bar->remove_node('wp-logo');
            $wp_admin_bar->remove_node('comments');
            $wp_admin_bar->remove_node('new-content');
        }
    }
}

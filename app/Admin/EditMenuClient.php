<?php

namespace App\Admin;

use App\Services\Config;

/**---------------------------------------------
 * 管理画面メニュー制御クラス（クライアント向け）
 * ---------------------------------------------
 * - 管理画面の表示メニューを権限別に制御する
 * - editor 以下のユーザーを対象に UI を簡略化する
 * - 制御内容は Config 設定により切り替える
 * - functions.php に直接書かない
 */
class EditMenuClient extends Admin
{
  private $hidden_client_menus;
  private $visible_custom_menus;
  private $opts;

  public function __construct()
  {
    $config                     = Config::get('admin.client_menu');
    $this->hidden_client_menus  = $config['hidden'];
    $this->visible_custom_menus = $config['visible'];
    $this->opts                 = $config['default_option'];
  }

  /**
   * 初期化処理
   */
  public function boot(): void
  {
    if (!$this->subjectRoles()) {
      return;
    }

    add_action('admin_menu', [$this, 'removeMenusForEditor'], 9999);
    add_action('admin_init', [$this, 'hideUpdateNoticeForEditor']);
    add_action('admin_bar_menu', [$this, 'customizeAdminBarForLimitedUsers'], 9999);
  }

  /**
   * 対象ユーザー判定
   */
  public function subjectRoles(): bool
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
    $hidden_menus  = $this->hidden_client_menus;
    $visible_menus = $this->visible_custom_menus;

    // カスタム投稿タイプのメニューを許可対象に追加
    if (!empty($visible_menus['post_type'])) {
      foreach ($visible_menus['post_type'] as $post_type) {
        $hidden_menus[] = 'edit.php?post_type=' . $post_type;
      }
    }

    // オプションページのメニューを許可対象に追加
    if (!empty($visible_menus['option'])) {
      foreach ($visible_menus['option'] as $option_page) {
        $hidden_menus[] = $option_page;
      }
    }

    global $menu;

    foreach ($menu as $key => $value) {
      $menu_slug = $value[2];
      $keep      = in_array($menu_slug, $hidden_menus);

      // サブメニューに許可項目がある場合は保持する
      if (!$keep && !empty($GLOBALS['submenu'][$menu_slug])) {
        foreach ($GLOBALS['submenu'][$menu_slug] as $submenu_item) {
          if (in_array($submenu_item[2], $hidden_menus)) {
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
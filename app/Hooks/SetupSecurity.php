<?php

declare(strict_types=1);

namespace App\Hooks;

use App\Interfaces\BootableWpHookInterface;

/**---------------------------------------------
 * セキュリティ強化フッククラス
 * ---------------------------------------------
 * - WordPress のバージョン情報・ユーザー情報の隠蔽
 * - REST API・XML-RPC の不要エンドポイント制限
 * - ログインエラーメッセージの汎用化
 * - コメント機能の完全無効化
 */
class SetupSecurity implements BootableWpHookInterface
{
  public function boot(): void
  {
    // バージョン情報の隠蔽
    remove_action('wp_head', 'wp_generator');
    add_filter('the_generator', '__return_empty_string');
    add_action('template_redirect', $this->removeGeneratorMetaTag(...));

    // REST API・XML-RPC
    add_filter('rest_endpoints', $this->removeUsersEndpoint(...));
    add_filter('xmlrpc_enabled', '__return_false');
    remove_action('wp_head', 'rest_output_link_wp_head');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');

    // ユーザー名隠蔽
    add_filter('login_errors', $this->obscureLoginError(...));
    add_action('template_redirect', $this->redirectAuthorArchive(...));

    // CSS/JS の ?ver= クエリパラメータを削除してバージョン推測を防ぐ
    add_filter('style_loader_src', $this->removeAssetVersion(...), 9999);
    add_filter('script_loader_src', $this->removeAssetVersion(...), 9999);

    // コメント機能の完全無効化
    add_action('admin_init', $this->disableComments(...));
    add_filter('comments_open', '__return_false', 20, 2);
    add_filter('pings_open', '__return_false', 20, 2);
    add_filter('comments_array', '__return_empty_array', 10, 2);
    remove_action('wp_head', 'feed_links_extra', 3);
    add_action('wp_before_admin_bar_render', $this->removeCommentsFromAdminBar(...));
  }

  /**
   * <meta name="generator"> タグをHTMLから除去する
   */
  public function removeGeneratorMetaTag(): void
  {
    ob_start(function (string $html): string {
      return preg_replace('/<meta\s+name=["\']generator["\'][^>]*>/i', '', $html) ?? $html;
    });
  }

  /**
   * REST API の /users エンドポイントを未認証アクセスから削除する
   *
   * 認証済みユーザーは引き続き利用可能にする。
   */
  public function removeUsersEndpoint(array $endpoints): array
  {
    if (! is_user_logged_in()) {
      unset($endpoints['/wp/v2/users']);
      unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    }

    return $endpoints;
  }

  /**
   * ログイン失敗時のエラーメッセージを汎用化する
   *
   * デフォルトではユーザー名・パスワードのどちらが違うかを示すメッセージが返り、
   * ユーザー名の存在確認に利用される恐れがある。
   */
  public function obscureLoginError(): string
  {
    return 'ユーザー名またはパスワードが正しくありません';
  }

  /**
   * /?author=N によるユーザー名露出をトップページへリダイレクトする
   */
  public function redirectAuthorArchive(): void
  {
    if (is_author()) {
      wp_redirect(home_url('/'), 301);
      exit;
    }
  }

  /**
   * コメント機能を管理画面から無効化する
   *
   * 既存投稿のコメントステータスをクローズし、メニュー・設定画面も除去する。
   */
  public function disableComments(): void
  {
    // 全投稿タイプのコメント・トラックバックサポートを削除
    foreach (get_post_types() as $post_type) {
      if (post_type_supports($post_type, 'comments')) {
        remove_post_type_support($post_type, 'comments');
        remove_post_type_support($post_type, 'trackbacks');
      }
    }

    // 管理メニューからコメントを削除
    remove_menu_page('edit-comments.php');

    // 設定 → ディスカッションを非表示
    remove_submenu_page('options-general.php', 'options-discussion.php');
  }

  /**
   * CSS/JS URL から ?ver= クエリパラメータを除去する
   */
  public function removeAssetVersion(string $src): string
  {
    return $src ? remove_query_arg('ver', $src) : $src;
  }

  /**
   * 管理バーのコメントリンクを削除する
   */
  public function removeCommentsFromAdminBar(): void
  {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('comments');
  }
}

<?php

namespace App\Hooks;

use App\Helpers\Path;
use App\Interfaces\BootableWpHookInterface;
use App\Services\Config;

/**---------------------------------------------
 * 管理画面オプションページ登録クラス
 * ---------------------------------------------
 * - 管理画面側で使用する Admin クラス
 * - オプションページの登録・リダイレクト・描画を一元管理する
 * - 表示可否は Config 設定により制御する
 * - add_menu_page を散らさない
 * - functions.php に管理画面ロジックを書かない
 * - 管理画面の表示有無を設定ファイル主導で切り替える
 * - View とロジックを分離する
 */
class RegisterOptionPage implements BootableWpHookInterface
{
  public function boot(): void
  {
    add_action('admin_menu', $this->register(...));
    add_action('admin_init', $this->redirect(...));
  }

  /**
   * 管理画面オプション登録
   */
  public function register(): void
  {
    foreach (Config::get('cms.option_pages') ?? [] as $key => $option) {
      if (empty($option['show'])) {
        continue;
      }

      add_menu_page(
        $option['page_title'],
        $option['menu_title'],
        $option['capability'] ?? 'manage_options',
        $option['slug'] ?? $key,
        fn() => $this->render($option),
        $option['icon'] ?? '',
        $option['position'] ?? null,
      );
    }
  }

  /**
   * ヘッダー送信前にリダイレクト処理を行う
   * admin_init タイミングで実行されるため wp_redirect() が使用可能
   */
  public function redirect(): void
  {
    $currentPage = $_GET['page'] ?? null;

    if (!$currentPage) {
      return;
    }

    foreach (Config::get('cms.option_pages') ?? [] as $key => $option) {
      $slug = $option['slug'] ?? $key;

      if ($slug !== $currentPage) {
        continue;
      }

      if (empty($option['redirect'])) {
        continue;
      }

      wp_safe_redirect(admin_url($option['redirect']));
      exit;
    }
  }

  /**
   * 管理画面 View を表示する
   * redirect キーが設定されている場合はリダイレクトのみ行い描画しない
   */
  private function render(array $option): void
  {
    $view = $option['view'] ?? null;

    if (!$view) {
      return;
    }

    $baseDir = Config::get('cms.option_view_dir') ?? Path::views('app/admin');
    $path    = Path::join(\rtrim($baseDir, '/'), $view);

    if (!file_exists($path)) {
      return;
    }

    include $path;
  }
}

<?php

namespace App\Hooks;

use App\Services\Config;

/**---------------------------------------------
 * アセット登録フッククラス
 * ---------------------------------------------
 * - フロント / 管理画面 / ログイン画面のアセットを一元管理する
 * - wp_enqueue_* をテンプレートや functions.php に散らさない
 * - アセット定義は Config 側に寄せる
 */
class Enqueue extends Hook
{
  private string $version;

  public function __construct()
  {
    $this->version = (string) Config::get('assets.version', '1.0.0');
  }

  /**
   * フック登録
   */
  public function boot(): void
  {
    add_action('wp_enqueue_scripts', [$this, 'enqueueFront']);
    add_action('admin_enqueue_scripts', [$this, 'enqueueAdmin']);
    add_action('login_enqueue_scripts', [$this, 'enqueueAdmin']);
  }

  /**
   * フロント用アセット
   */
  public function enqueueFront(): void
  {
    $this->enqueueJquery();
    wp_dequeue_style('wp-block-library');
    $this->enqueueStyles(Config::get('assets.css'));
    $this->enqueueScripts(Config::get('assets.js'));
  }

  /**
   * 管理画面用アセット
   */
  public function enqueueAdmin(): void
  {
    wp_enqueue_media();

    $this->enqueueJquery();
    $this->enqueueStyles(Config::get('assets.admin-css'));
    $this->enqueueScripts(Config::get('assets.admin-js'));
  }

  /**
   * jQuery差し替え
   */
  private function enqueueJquery(): void
  {
    $jquery = Config::get('assets.jquery');
    if (!$jquery)
      return;

    wp_deregister_script('jquery');
    wp_enqueue_script('jquery', $jquery, [], null, true);
  }

  /**
   * CSSまとめて登録
   */
  private function enqueueStyles(?array $styles): void
  {
    if (empty($styles))
      return;

    foreach ($styles as $handle => $src) {
      wp_enqueue_style(
        is_string($handle) ? $handle : md5($src),
        $src,
        [],
        $this->version
      );
    }
  }

  /**
   * JSまとめて登録
   */
  private function enqueueScripts(?array $scripts): void
  {
    if (empty($scripts))
      return;

    foreach ($scripts as $handle => $src) {
      wp_enqueue_script(
        is_string($handle) ? $handle : md5($src),
        $src,
        ['jquery'],
        $this->version,
        true
      );
    }
  }
}
<?php

namespace App\Hooks;

use App\Interfaces\BootableWpHookInterface;
use App\Services\Config;

/**---------------------------------------------
 * アセット登録フッククラス
 * ---------------------------------------------
 * - フロント / 管理画面 / ログイン画面のアセットを一元管理する
 * - wp_enqueue_* をテンプレートや functions.php に散らさない
 * - アセット定義は Config 側に寄せる
 */
class AssetsEnqueuer implements BootableWpHookInterface
{
  private readonly string $version;

  public function __construct()
  {
    $this->version = (string) Config::get('assets.version', '1.0.0');
  }

  public function boot(): void
  {
    add_action('wp_enqueue_scripts', $this->enqueueFront(...));
    add_action('admin_enqueue_scripts', $this->enqueueAdmin(...));
    add_action('login_enqueue_scripts', $this->enqueueAdmin(...));
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

    if (is_admin()) {
      return;
    }

    $jquery = Config::get('assets.jquery');
    if (!$jquery) {
      return;
    }

    wp_deregister_script('jquery');
    wp_enqueue_script('jquery', $jquery, [], null, true);
  }

  /**
   * CSSまとめて登録
   */
  private function enqueueStyles(?array $styles): void
  {
    if (empty($styles)) {
      return;
    }

    foreach ($styles as $handle => $src) {
      wp_enqueue_style(
        \is_string($handle) ? $handle : \md5((string) $src),
        $src,
        [],
        $this->version
      );
    }
  }

  /** @var string[] type="module" として出力するハンドル */
  private array $moduleHandles = [];

  /**
   * JSまとめて登録
   * $scripts の値は string（src）か ['src' => string, 'module' => bool] を受け付ける
   */
  private function enqueueScripts(?array $scripts): void
  {
    if (empty($scripts)) {
      return;
    }

    foreach ($scripts as $handle => $script) {
      $src    = \is_array($script) ? ($script['src'] ?? '') : $script;
      $module = \is_array($script) && !empty($script['module']);
      $handle = \is_string($handle) ? $handle : \md5((string) $src);

      wp_enqueue_script(
        $handle,
        $src,
        ['jquery'],
        $this->version,
        true
      );

      if ($module) {
        $this->moduleHandles[] = $handle;
      }
    }

    add_filter('script_loader_tag', [$this, 'addModuleType'], 10, 2);
  }

  /**
   * 指定ハンドルの script タグに type="module" を付与
   */
  public function addModuleType(string $tag, string $handle): string
  {
    if (!\in_array($handle, $this->moduleHandles, true)) {
      return $tag;
    }

    // 既存の type 属性を除去してから module を付与
    $tag = \preg_replace('/\stype=(["\']).*?\1/', '', $tag);

    return \str_replace('<script ', '<script type="module" ', $tag);
  }
}

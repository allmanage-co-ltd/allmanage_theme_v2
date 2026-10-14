<?php

namespace App\Hooks;

use App\Interfaces\BootableWpHookInterface;

/**---------------------------------------------
 * 環境バッジ表示フッククラス
 * ---------------------------------------------
 * - 管理バーに「ローカル」「テスト環境」バッジを表示し、誤環境での操作を防ぐ
 * - 本番環境では非表示
 * - ホスト名の部分一致で自動判定（フィルターで上書き可能）
 *
 * カスタマイズ：
 *   // 環境判定ルールを上書き
 *   add_filter('env_badge/detect', function ($env) {
 *       if (defined('WP_ENV')) return WP_ENV; // 'local' | 'staging' | 'production'
 *       return $env;
 *   });
 *
 *   // ステージングのホスト条件を追加
 *   add_filter('env_badge/staging_hosts', function ($hosts) {
 *       $hosts[] = 'staging.example.com';
 *       return $hosts;
 *   });
 */
class EnvBadge implements BootableWpHookInterface
{
  /** ローカル環境と判定するホスト部分文字列 */
  private const LOCAL_HOSTS = [
    'localhost',
    '127.0.0.1',
    '.local',
    '.test',
  ];

  /** ステージング環境と判定するホスト部分文字列 */
  private const STAGING_HOSTS = [
    'web-checker',
    '.check-xserver.jp',
    'staging.',
    'stg.',
    'dev.',
    'preview.',
  ];

  /** @var array{label: string, color: string, bg: string} */
  private array $env;

  public function boot(): void
  {
    add_action('after_setup_theme', function () {
      if (!is_admin() && !is_admin_bar_showing()) {
        return;
      }

      $this->env = $this->resolve();

      // 本番は何も表示しない
      if ($this->env['label'] === '') {
        return;
      }

      add_action('admin_bar_menu', [$this, 'addNode'], 0);
      add_action('wp_head', [$this, 'inlineStyle']);
      add_action('admin_head', [$this, 'inlineStyle']);
    });
  }

  /**
   * 管理バーにバッジノードを追加する
   */
  public function addNode(\WP_Admin_Bar $bar): void
  {
    $bar->add_node([
      'id'    => 'env-badge',
      'title' => '<span class="env-badge__label">' . esc_html($this->env['label']) . '</span>',
      'href'  => false,
      'meta'  => ['class' => 'env-badge'],
    ]);
  }

  /**
   * バッジ用のインラインスタイルを出力する
   */
  public function inlineStyle(): void
  {
    $bg    = esc_attr($this->env['bg']);
    $color = esc_attr($this->env['color']);
    echo '<style>
#wpadminbar #wp-admin-bar-env-badge { pointer-events: none; }
#wpadminbar #wp-admin-bar-env-badge > .ab-item { padding: 0 10px; }
#wpadminbar #wp-admin-bar-env-badge .env-badge__label {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 3px;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .05em;
    line-height: 1.6;
    background: ' . $bg . ';
    color: ' . $color . ';
    vertical-align: middle;
}
</style>';
  }

  /**
   * ホスト名から環境を判定して label / color / bg を返す
   *
   * @return array{label: string, color: string, bg: string}
   */
  private function resolve(): array
  {
    $detected = apply_filters('env_badge/detect', $this->detect());

    $map = [
      'local'      => ['label' => 'ローカル',    'bg' => '#4caf50', 'color' => '#fff'],
      'staging'    => ['label' => 'テスト環境', 'bg' => '#ff9800', 'color' => '#fff'],
      'production' => ['label' => '',             'bg' => '',        'color' => ''],
    ];

    return $map[$detected] ?? $map['production'];
  }

  /**
   * ホスト名の部分一致でローカル → ステージング → 本番の順に判定する
   */
  private function detect(): string
  {
    $host = isset($_SERVER['HTTP_HOST']) ? strtolower((string) $_SERVER['HTTP_HOST']) : '';

    $local_hosts = apply_filters('env_badge/local_hosts', self::LOCAL_HOSTS);
    foreach ($local_hosts as $pattern) {
      if (strpos($host, $pattern) !== false) {
        return 'local';
      }
    }

    $staging_hosts = apply_filters('env_badge/staging_hosts', self::STAGING_HOSTS);
    foreach ($staging_hosts as $pattern) {
      if (strpos($host, $pattern) !== false) {
        return 'staging';
      }
    }

    return 'production';
  }
}

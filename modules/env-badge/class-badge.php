<?php

/**
 * EnvBadge\Badge — 環境バッジの描画クラス
 *
 * - 現在のホスト名から環境（local / staging / production）を判定
 * - 管理バーに色付きバッジを追加
 * - フロントエンドでも管理バーが表示されていれば動作する
 */

namespace EnvBadge;

if (!defined('ABSPATH')) {
  exit;
}

class Badge
{
  /** @var array{label: string, color: string, bg: string} */
  private $env;

  public function boot(): void
  {
    $this->env = $this->resolve();

    // 本番は何も表示しない
    if ($this->env['label'] === '') {
      return;
    }

    add_action('admin_bar_menu', [$this, 'addNode'], 0);
    add_action('wp_head', [$this, 'inlineStyle']);
    add_action('admin_head', [$this, 'inlineStyle']);
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
    // フィルターで外部から上書き可能
    $detected = apply_filters('env_badge/detect', $this->detect());

    $map = [
      'local'      => ['label' => 'ローカル',       'bg' => '#4caf50', 'color' => '#fff'],
      'staging'    => ['label' => 'テスト環境',    'bg' => '#ff9800', 'color' => '#fff'],
      'production' => ['label' => '',               'bg' => '',        'color' => ''],
    ];

    return $map[$detected] ?? $map['production'];
  }

  /**
   * ホスト名の部分一致でローカル → ステージング → 本番の順に判定する
   */
  private function detect(): string
  {
    $host = isset($_SERVER['HTTP_HOST']) ? strtolower((string) $_SERVER['HTTP_HOST']) : '';

    $local_hosts = apply_filters('env_badge/local_hosts', ENV_BADGE_LOCAL_HOSTS);
    foreach ($local_hosts as $pattern) {
      if (strpos($host, $pattern) !== false) {
        return 'local';
      }
    }

    $staging_hosts = apply_filters('env_badge/staging_hosts', ENV_BADGE_STAGING_HOSTS);
    foreach ($staging_hosts as $pattern) {
      if (strpos($host, $pattern) !== false) {
        return 'staging';
      }
    }

    return 'production';
  }
}

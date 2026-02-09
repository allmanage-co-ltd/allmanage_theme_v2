<?php

namespace App\Services\UI;

/**---------------------------------------------
 * Datepicker用UIヘルパークラス
 * ---------------------------------------------
 * - flatpickr を使った日付入力UIを初期化するための補助クラス
 * - HTML側では `.js-datepicker` クラスを付与するだけで利用可能
 */
class Datepicker
{
  /**
   *  flatpickr に渡すオプション配列
   *  例:
   *  [
   *    'dateFormat' => 'Y-m-d',
   *    'minDate'    => 'today',
   *    'disable'    => ['2024-01-01'],
   *  ]
   */
  private $defaults = [
    'dateFormat' => 'Y年m月d日',
    'locale'     => 'ja',
    'yearRange'  => '-50:+0',
    'minDate'    => 0
  ];
  private $config;
  private $configJson;

  public function __construct(array $options = [])
  {
    // オプションをマージ
    $this->config = array_merge($this->defaults, $options);

    // JSに渡すためJSON化（日本語保持）
    $this->configJson = json_encode($this->config, JSON_UNESCAPED_UNICODE);
  }

  /**
   * 初期化
   */
  public function boot(): void
  {
    add_action('wp_footer', [$this, 'render'], 9998);
  }

  /**
   *  footerに書き込み
   */
  public function render(): void
  {
    echo <<<HTML
    <script>
      if ($('.js-datepicker').length){
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr('.js-datepicker', {$this->configJson});
        });
      }
    </script>
HTML;
  }
}
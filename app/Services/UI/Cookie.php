<?php

namespace App\Services\UI;

/**---------------------------------------------
 * Cookieポップアップ生成クラス
 * ---------------------------------------------
 * - Cookie利用に関する同意UIを出力する
 * - 初回アクセス時のみ表示される
 * - 同意 / 拒否の結果を Cookie に保存する
 * - 同意状態が確定したら即リロードして反映
 */
class Cookie
{
  private $days;
  private $link;

  public function __construct($days = 365, $link = '/privacy')
  {
    $this->days = $days;
    $this->link = $link;
  }

  /**
   * Cookie同意UIを生成する
   */
  public function render(): string
  {
    $link = htmlspecialchars($this->link, ENT_QUOTES, 'UTF-8');

    // 使用するCookie名
    $cookie_name = 'cookie_consent';

    // ボタン押下状態の判定
    $button_pressed = null;

    // Cookie許可ボタンが押された場合
    if (isset($_POST['accept_cookies'])) {
      $button_pressed = 'accepted';

      // Cookie拒否ボタンが押された場合
    } elseif (isset($_POST['cancel_cookies'])) {
      $button_pressed = 'rejected';
    }

    /**
     * 同意 or 拒否が確定した場合の処理
     *
     * - CookieをPHP側でセット
     * - JS側でも max-age を指定して明示的に反映
     * - リロードして即座に表示状態を更新
     */
    if ($button_pressed) {
      $expiry  = time() + ($this->days * 24 * 60 * 60);
      $max_age = $this->days * 24 * 60 * 60;
      @setcookie($cookie_name, $button_pressed, $expiry, '/');
      echo "<script>
      document.cookie = '{$cookie_name}={$button_pressed}; path=/; max-age={$max_age}';
      window.location.href = window.location.href;
    </script>";
      exit;
    }

    /**
     * 既に同意 or 拒否済みの場合
     *
     * - Cookieが存在すればUIは表示しない
     */
    if (isset($_COOKIE[$cookie_name])) {
      return '';
    }

    return <<<HTML
<div class="cookie-consent">
  <p>
    <span>当サイトではCookieを使用します。</span><br>
    Cookieの使用に関する詳細は「<a href="{$link}">プライバシーポリシー</a>」をご覧ください。
  </p>
  <form method="post" style="display: inline;">
    <button type="submit" name="accept_cookies">Cookieを許可する</button>
    <button type="submit" name="cancel_cookies">Cookieを拒否する</button>
  </form>
</div>
HTML;
  }
}
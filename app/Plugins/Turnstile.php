<?php

declare(strict_types=1);

namespace App\Plugins;

use App\Interfaces\BootableWpHookInterface;
use App\Services\Config;
use WP_Post;

/**---------------------------------------------
 * Cloudflare Turnstile 連携クラス
 * ---------------------------------------------
 * - config/recaptcha.php の turnstile 設定をもとに動作する
 * - MW WP Form / ログインフォームへの Turnstile 挿入・検証を担う
 * - フォームの追加は config の forms にスラッグ+フォームIDを足すだけ
 * - ログイン対応は config の login.use_add_turnstile を true にするだけ
 */
class Turnstile implements BootableWpHookInterface
{
  /**
   * 初期化処理
   */
  public function boot(): void
  {
    \define('TURNSTILE_SECRET_KEY', Config::get('recaptcha.turnstile.secretkey') ?? '');

    if (Config::get('recaptcha.turnstile.mwform.use_add_turnstile')) {
      \add_action('wp_enqueue_scripts', $this->enqueueMwFormScript(...));

      foreach (\array_values($this->resolveMwFormIds()) as $form_id) {
        \add_filter(
          'mwform_validation_mw-wp-form-' . $form_id,
          $this->validateMwForm(...),
          10,
          2
        );
      }
    }

    if (Config::get('recaptcha.turnstile.login.use_add_turnstile')) {
      \add_action('login_form', $this->renderLoginWidget(...));
      \add_action('login_head', $this->renderLoginStyle(...));
      \add_action('wp_authenticate_user', $this->validateLogin(...), 10, 2);
    }
  }

  /**
   * config の mwform.forms（スラッグ => フォームID）を
   * is_page() で使える（ページID => フォームID）形式に変換する
   *
   * @return array<int, int>
   */
  private function resolveMwFormIds(): array
  {
    $result = [];

    foreach (Config::get('recaptcha.turnstile.mwform.forms') ?? [] as $slug => $form_id) {
      $page = \get_page_by_path((string) $slug);
      if ($page instanceof WP_Post) {
        $result[$page->ID] = (int) $form_id;
      }
    }

    return $result;
  }

  /**
   * Turnstile スクリプトを対象ページのみ読み込む
   */
  public function enqueueMwFormScript(): void
  {
    if (!\is_page(\array_keys($this->resolveMwFormIds()))) {
      return;
    }

    \wp_enqueue_script(
      'cloudflare-turnstile',
      'https://challenges.cloudflare.com/turnstile/v0/api.js',
      [],
      null,
      ['strategy' => 'defer', 'in_footer' => false]
    );
  }

  /**
   * MW WP Form バリデーション処理
   *
   * - 「戻る」遷移時はスキップ
   * - 「送信（確認→完了）」遷移時はスキップ
   * - トークン未取得・検証失敗時にエラーをセット
   */
  public function validateMwForm(mixed $Validation, mixed $data): mixed
  {
    static $is_verified = false;

    if ($is_verified || empty($data)) {
      return $Validation;
    }

    $has_word = function (array $words, array $form_data): bool {
      foreach ($form_data as $val) {
        if (\is_array($val)) continue;
        if (\in_array($val, $words, true)) return true;
      }
      return false;
    };

    $back_words   = Config::get('recaptcha.turnstile.mwform.back_words') ?? [];
    $submit_words = Config::get('recaptcha.turnstile.mwform.submit_words') ?? [];

    if ($has_word($back_words, $data) || (isset($data['submitBack']) && $data['submitBack'] === 'back')) {
      return $Validation;
    }

    if ($has_word($submit_words, $data)) {
      return $Validation;
    }

    return $this->verify($Validation, 'turnstile-check');
  }

  /**
   * ログインページの <head> に iframe 幅強制スタイルを出力する
   */
  public function renderLoginStyle(): void
  {
    echo '<style>.login form { padding: 26px 9px; } .cf-turnstile iframe { width: 100% !important; min-width: unset !important; max-width: 100% !important; }</style>';
  }

  /**
   * ログインフォームに Turnstile ウィジェットを出力する
   */
  public function renderLoginWidget(): void
  {
    $sitekey = Config::get('recaptcha.turnstile.sitekey') ?? '';
    echo '<div class="cf-turnstile" data-sitekey="' . \esc_attr($sitekey) . '" style="margin-bottom:1rem; width:100%;"></div>';
    echo '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" defer></script>';
  }

  /**
   * ログインフォームの Turnstile トークンを検証する
   */
  public function validateLogin(mixed $user, mixed $password): mixed
  {
    $result = $this->callVerifyApi();

    if ($result === null || !$result['success']) {
      return new \WP_Error(
        'turnstile_failed',
        Config::get('recaptcha.turnstile.messages.turnstile_failed') ?? ''
      );
    }

    return $user;
  }

  /**
   * Turnstile トークンを検証し、失敗時は Validation にエラーをセットして返す
   * MW WP Form のバリデーション処理から呼ぶ共通メソッド
   */
  private function verify(mixed $Validation, string $rule_key): mixed
  {
    $msg_no_token = Config::get('recaptcha.turnstile.messages.no_token') ?? '';
    $msg_failed   = Config::get('recaptcha.turnstile.messages.turnstile_failed') ?? '';

    $token = isset($_POST['cf-turnstile-response'])
      ? \sanitize_text_field(\wp_unslash($_POST['cf-turnstile-response']))
      : '';

    if (empty($token)) {
      $Validation->set_rule($rule_key, 'no_token', ['message' => $msg_no_token]);
      return $Validation;
    }

    $result = $this->callVerifyApi($token);

    if ($result === null || !$result['success']) {
      $Validation->set_rule($rule_key, 'turnstile_failed', ['message' => $msg_failed]);
    }

    return $Validation;
  }

  /**
   * Cloudflare Turnstile API を呼び出してトークンを検証する
   *
   * @return array<string, mixed>|null  失敗時は null
   */
  private function callVerifyApi(?string $token = null): ?array
  {
    $token ??= isset($_POST['cf-turnstile-response'])
      ? \sanitize_text_field(\wp_unslash($_POST['cf-turnstile-response']))
      : '';

    $response = \wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
      'body' => [
        'secret'   => TURNSTILE_SECRET_KEY,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
      ],
    ]);

    if (\is_wp_error($response)) {
      return null;
    }

    return \json_decode(\wp_remote_retrieve_body($response), true);
  }
}

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
 *
 * セッション管理フロー:
 * - confirm: Turnstile API 検証 → 成功時にセッションへ verified フラグを保存
 * - complete: セッションの verified フラグを確認 → なければバリデーションエラー
 * - back: セッションの verified フラグを破棄
 */
class Turnstile implements BootableWpHookInterface
{
  /** セッションキーのプレフィックス */
  private const SESSION_PREFIX = 'turnstile_verified_';

  /** 検証済みフラグの有効期限（秒） */
  private const SESSION_TTL = 300;

  /**
   * 初期化処理
   */
  public function boot(): void
  {
    \define('TURNSTILE_SECRET_KEY', Config::get('recaptcha.turnstile.secretkey') ?? '');

    // セッション開始（mwform バリデーション前に必要）
    \add_action('init', function (): void {
      if (\session_status() === \PHP_SESSION_NONE) {
        \session_start();
      }
    }, 1);

    if (Config::get('recaptcha.turnstile.mwform.use_add_turnstile')) {
      \add_action('wp_enqueue_scripts', $this->enqueueMwFormScript(...));

      foreach (\array_values($this->resolveMwFormIds()) as $form_id) {
        $form_key = 'mw-wp-form-' . $form_id;

        \add_filter(
          'mwform_validation_' . $form_key,
          $this->validateMwForm(...),
          10,
          3
        );

        // G: バリデーションをすり抜けた場合の最終防衛ライン（メール送信停止）
        \add_filter('mwform_mail_' . $form_key, $this->blockMail(...), 10, 3);
        \add_filter('mwform_auto_mail_' . $form_key, $this->blockMail(...), 10, 3);
      }

      // H: メール停止フラグが立っていれば入力ページへリダイレクト
      \add_action('template_redirect', $this->redirectOnBlocked(...), 1);

      // I: 入力ページ上部にエラーメッセージを表示
      \add_filter('the_content', $this->showBlockedError(...), 20);
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
   * MW WP Form の post_condition で遷移状態を判定し、セッションで検証済みフラグを管理する。
   * - confirm: Turnstile API 検証 → 成功時にセッション保存
   * - complete: セッションの verified フラグ確認 → なければエラー
   * - back: セッションの verified フラグを破棄
   *
   * @param mixed $Validation MW WP Form Validation オブジェクト
   * @param mixed $data       POST データ配列
   * @param mixed $Data       MW WP Form Data オブジェクト
   */
  public function validateMwForm(mixed $Validation, mixed $data, mixed $Data): mixed
  {
    if (empty($data) || !\is_object($Data)) {
      return $Validation;
    }

    $session_key    = self::SESSION_PREFIX . \md5(\current_filter());
    $post_condition = $Data->get_post_condition();

    // 「戻る」: 検証済みフラグを破棄して何もしない
    if ($post_condition === 'back') {
      unset($_SESSION[$session_key]);
      return $Validation;
    }

    // 「確認画面 → 完了（メール送信）」: セッションの verified フラグを検証する
    if ($post_condition === 'complete') {
      return $this->validateSessionOnComplete($Validation, $Data, $session_key);
    }

    // 「入力画面 → 確認画面」: 古いフラグを破棄してから Turnstile 検証を行う
    if ($post_condition === 'confirm') {
      unset($_SESSION[$session_key]);
      return $this->verifyAndSaveSession($Validation, $session_key, $Data);
    }

    return $Validation;
  }

  /**
   * complete 遷移時のセッション検証
   *
   * セッションに verified フラグがなければバリデーションエラーをセットする
   *
   * @param mixed  $Validation  MW WP Form Validation オブジェクト
   * @param mixed  $Data        MW WP Form Data オブジェクト
   * @param string $session_key セッションキー
   */
  private function validateSessionOnComplete(mixed $Validation, mixed $Data, string $session_key): mixed
  {
    $session = $_SESSION[$session_key] ?? [];

    if (empty($session['verified'])) {
      $_SESSION['turnstile_blocked'] = true;
      return $Validation;
    }

    $verified_at = (int) ($session['verified_at'] ?? 0);
    if (!$verified_at || (\time() - $verified_at) > self::SESSION_TTL) {
      unset($_SESSION[$session_key]);
      $_SESSION['turnstile_blocked'] = true;
      return $Validation;
    }

    return $Validation;
  }

  /**
   * confirm 遷移時の Turnstile API 検証 + セッション保存
   *
   * バリデーションフィルタでのエラー表示ではなく、検証失敗時は即リダイレクトフラグを立てる。
   * MW WP Form の hidden フィールドが常に POST 値を持つため、$Data->set() による
   * 空値セットが効かないケースへの対策として、H ライン（template_redirect）に委譲する。
   *
   * @param mixed  $Validation  MW WP Form Validation オブジェクト
   * @param string $session_key セッションキー
   * @param mixed  $Data        MW WP Form Data オブジェクト
   */
  private function verifyAndSaveSession(mixed $Validation, string $session_key, mixed $Data): mixed
  {
    $msg_no_token = Config::get('recaptcha.turnstile.messages.no_token') ?? '';
    $msg_failed   = Config::get('recaptcha.turnstile.messages.turnstile_failed') ?? '';

    $token = isset($_POST['cf-turnstile-response'])
      ? \sanitize_text_field(\wp_unslash($_POST['cf-turnstile-response']))
      : '';

    if (empty($token)) {
      // noempty は空文字でもエラーになる（required は null のみエラー）
      $Data->set('turnstile-check', '');
      $Validation->set_rule('turnstile-check', 'noempty', ['message' => $msg_no_token]);
      \error_log('[Turnstile] Data spl_id=' . spl_object_id($Data));
      // Validation_Rules シングルトンが持つ noempty オブジェクトの Data インスタンスIDを確認
      $form_key = \str_replace('mwform_validation_', '', \current_filter());
      $vRules   = \MW_WP_Form_Validation_Rules::instantiation($form_key);
      $rules    = $vRules->get_validation_rules();
      if (isset($rules['noempty'])) {
        $noempty_data = (new \ReflectionProperty(\MW_WP_Form_Abstract_Validation_Rule::class, 'Data'))->getValue($rules['noempty']);
        \error_log('[Turnstile] noempty->Data spl_id=' . ($noempty_data ? spl_object_id($noempty_data) : 'null'));
        \error_log('[Turnstile] noempty->Data->get=' . var_export($noempty_data ? $noempty_data->get('turnstile-check') : 'N/A', true));
      }
      return $Validation;
    }

    $result = $this->callVerifyApi($token);

    if ($result === null || !$result['success']) {
      $Data->set('turnstile-check', '');
      $Validation->set_rule('turnstile-check', 'noempty', ['message' => $msg_failed]);
      return $Validation;
    }
    $_SESSION[$session_key] = [
      'verified'    => true,
      'verified_at' => \time(),
    ];

    return $Validation;
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
   * G: バリデーションをすり抜けた場合の最終防衛ライン
   *
   * セッションに verified フラグがなければメール宛先を空にして送信を無効化し、
   * 入力ページへ戻すフラグをセッションに立てる。
   *
   * @param mixed $Mail   MW WP Form Mail オブジェクト
   * @param mixed $values フォームデータ
   * @param mixed $Data   MW WP Form Data オブジェクト
   */
  public function blockMail(mixed $Mail, mixed $values, mixed $Data): mixed
  {
    // current_filter() は mwform_mail_* / mwform_auto_mail_* なので validation キーに変換する
    $validation_key = \str_replace(
      ['mwform_auto_mail_', 'mwform_mail_'],
      'mwform_validation_',
      \current_filter()
    );
    $session_key = self::SESSION_PREFIX . \md5($validation_key);

    if (!empty($_SESSION[$session_key]['verified'])) {
      return $Mail;
    }

    \error_log('[Turnstile] verified フラグなしのためメール送信を停止 (' . \current_filter() . ')');

    $Mail->to  = '';
    $Mail->cc  = '';
    $Mail->bcc = '';

    $_SESSION['turnstile_blocked'] = true;

    return $Mail;
  }

  /**
   * H: blocked フラグが立っていれば入力ページへリダイレクト
   *
   * confirm 時の Turnstile 検証失敗・complete 時のセッション不正の両方をここで捌く。
   * template_redirect はバリデーションフィルタ後・テンプレート出力前に発火するため、
   * 確認画面が表示される前に入力ページへ戻せる。
   */
  public function redirectOnBlocked(): void
  {
    if (empty($_SESSION['turnstile_blocked'])) {
      return;
    }

    unset($_SESSION['turnstile_blocked']);

    // is_page() で現在のフォームページ URL を特定する（POST中でも利用可）
    $input_url = null;
    foreach ($this->resolveMwFormIds() as $page_id => $form_id) {
      if (\is_page($page_id)) {
        $input_url = \get_permalink($page_id);
        break;
      }
    }
    $input_url ??= \wp_get_referer() ?: \home_url('/');

    \wp_safe_redirect(\add_query_arg('turnstile_error', '1', $input_url));
    exit;
  }

  /**
   * I: 入力ページ上部にエラーメッセージを表示する
   *
   * @param string $content 投稿コンテンツ
   */
  public function showBlockedError(string $content): string
  {
    if (empty($_GET['turnstile_error'])) {
      return $content;
    }

    $message = '<div class="turnstile-error" style="padding:16px;margin-bottom:24px;color:#b00;font-weight:bold;background-color:#ffeaea;border:1px solid #b00;">'
      . '認証情報を確認できませんでした。お手数ですが、もう一度最初から入力してください。'
      . '</div>';

    return $message . $content;
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

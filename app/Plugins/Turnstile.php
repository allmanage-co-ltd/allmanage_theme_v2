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
 * - confirm:    Turnstile API 検証 → 成功時にセッションへ verified フラグを保存
 *               失敗時は MW_WP_Form_Data にエラーをセット（入力ページへ戻す）
 * - complete:   verified/sent フラグ確認 → 正規通過時に complete_passed を記録
 * - after_send: verified → sent に切り替え（完了画面表示のみ許可・再送信防止）
 * - blockMail:  complete_passed フラグがなければメール送信停止
 * - shutdown:   sent フラグを消費して二重送信防止
 * - back:       verified フラグを破棄
 */
class Turnstile implements BootableWpHookInterface
{
  /** セッションキーのプレフィックス */
  private const SESSION_PREFIX = 'turnstile_verified_';

  /** 検証済みフラグの有効期限（秒）: 確認画面滞在を考慮して余裕を持たせる */
  private const VERIFY_TTL = 1800;

  /** 送信済みフラグの有効期限（秒）: 完了画面を表示する直後のリクエストのみ */
  private const SENT_TTL = 30;

  /**
   * リクエスト内の状態を保持するプロパティ
   *
   * MW WP Form は同一リクエスト内でバリデーションを複数回実行する。
   * セッション（リクエストをまたぐ状態）とは別に、
   * 「このリクエストで検証を通過したか」をメモリ上に保持する。
   *
   * complete_passed: verified 経由の正規通過。メール送信を許可する。
   * sent_replay:     sent 経由の通過。完了画面の表示のみ許可し、メール送信は許可しない。
   *
   * @var array<string, mixed>
   */
  private array $request_state = [
    'complete_passed'  => false,
    'sent_replay'      => false,
    'sent_replay_keys' => [],
    'error_raised'     => false,
  ];

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

      foreach ($this->resolveMwFormIds() as $page_id => $form_id) {
        $form_key = 'mw-wp-form-' . $form_id;

        // confirm: Turnstile 検証 / back: セッション破棄 / complete: セッション確認
        \add_filter(
          'mwform_validation_' . $form_key,
          $this->validateMwForm(...),
          10,
          3
        );

        // メール送信後に verified → sent へ切り替える
        \add_action(
          'mwform_after_send_' . $form_key,
          $this->afterSend(...),
          10,
          1
        );

        // Turnstile 未検証なら入力ページへリダイレクトさせる
        \add_filter(
          'mwform_redirect_url_' . $form_key,
          $this->filterRedirectUrl(...),
          10,
          2
        );

        // バリデーションをすり抜けた場合の最終防衛ライン（メール送信停止）
        \add_filter('mwform_mail_' . $form_key, $this->blockMail(...), 10, 3);
        \add_filter('mwform_auto_mail_' . $form_key, $this->blockMail(...), 10, 3);
      }

      // 入力ページ上部にエラーメッセージを表示
      \add_filter('the_content', $this->showBlockedError(...), 20);

      // 送信済みフラグをリクエスト終了時に消費する
      \add_action('shutdown', $this->consumeSentFlags(...), 1);
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
   * confirm:  Turnstile API 検証のみ（失敗時はエラーをセット）
   * back:     セッションの verified フラグを破棄
   * complete: セッションの verified/sent フラグ確認
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

    if ($post_condition === 'back') {
      unset($_SESSION[$session_key]);
      return $Validation;
    }

    if ($post_condition === 'complete') {
      return $this->validateSessionOnComplete($Validation, $session_key);
    }

    if ($post_condition === 'confirm') {
      return $this->verifyAndSaveSession($Validation, $session_key);
    }

    return $Validation;
  }

  /**
   * complete 遷移時のセッション検証
   *
   * 通過条件は2種類あり、権限が異なる。
   *
   * 1. sent フラグ:     メール送信完了後の完了画面表示リクエストのみ通過させる。
   *                     complete_passed は立てないのでメール送信は許可しない。
   * 2. verified フラグ: confirm 時の正規検証済み状態。complete_passed を立ててメール送信を許可する。
   *
   * @param mixed  $Validation  MW WP Form Validation オブジェクト
   * @param string $session_key セッションキー
   */
  private function validateSessionOnComplete(mixed $Validation, string $session_key): mixed
  {
    // 同一リクエスト内の複数回呼び出し対策
    if ($this->request_state['complete_passed'] || $this->request_state['sent_replay']) {
      return $Validation;
    }

    $session       = $_SESSION[$session_key] ?? [];
    $error_message = Config::get('recaptcha.turnstile.messages.no_token') ?? 'スパム対策のチェックを行ってください。';

    // sent フラグ: 完了画面を表示する後続リクエストのため通過させる（メール送信は不可）
    if (!empty($session['sent'])) {
      $sent_at = (int) ($session['sent_at'] ?? 0);

      if ($sent_at && (\time() - $sent_at) <= self::SENT_TTL) {
        $this->request_state['sent_replay']          = true;
        $this->request_state['sent_replay_keys'][]   = $session_key;
        return $Validation;
      }

      // sent の有効期限切れ: フラグを破棄してエラーへ
      unset($_SESSION[$session_key]);
      $session = [];
    }

    if (empty($session['verified'])) {
      $this->setMwFormValidationError($error_message);
      $_SESSION['turnstile_error_flash'] = true;
      return $Validation;
    }

    $verified_at = (int) ($session['verified_at'] ?? 0);
    if (!$verified_at || (\time() - $verified_at) > self::VERIFY_TTL) {
      unset($_SESSION[$session_key]);
      $this->setMwFormValidationError($error_message);
      $_SESSION['turnstile_error_flash'] = true;
      return $Validation;
    }

    // 正規通過: リクエスト状態にメール送信許可を記録
    $this->request_state['complete_passed'] = true;

    return $Validation;
  }

  /**
   * confirm 遷移時の Turnstile API 検証 + セッション保存
   *
   * 失敗時: MW_WP_Form_Data にバリデーションエラーを直接セットして
   *        MW WP Form 自身に入力ページへ戻させる（view_flg = 'input' のまま保持）
   * 成功時: セッションに verified フラグを保存して確認画面へ進む
   *
   * MW WP Form が同一リクエストで複数回バリデーションを呼ぶ場合があるため、
   * セッションに verified フラグがあれば再検証をスキップする。
   *
   * @param mixed  $Validation  MW WP Form Validation オブジェクト
   * @param string $session_key セッションキー
   */
  private function verifyAndSaveSession(mixed $Validation, string $session_key): mixed
  {
    // 検証済みならスキップ（MWF が同一リクエストで複数回バリデーションを呼ぶ対策）
    if (!empty($_SESSION[$session_key]['verified'])) {
      return $Validation;
    }

    // 前回の送信済みフラグが残っている場合は破棄する
    if (!empty($_SESSION[$session_key]['sent'])) {
      unset($_SESSION[$session_key]);
    }

    $token = isset($_POST['cf-turnstile-response'])
      ? \sanitize_text_field(\wp_unslash($_POST['cf-turnstile-response']))
      : '';

    $error_message = Config::get('recaptcha.turnstile.messages.no_token') ?? 'スパム対策のチェックを行ってください。';

    if (empty($token)) {
      $this->setMwFormValidationError($error_message);
      $_SESSION['turnstile_error_flash'] = true;
      return $Validation;
    }

    $result = $this->callVerifyApi($token);

    if ($result === null || !$result['success']) {
      $this->setMwFormValidationError($error_message);
      $_SESSION['turnstile_error_flash'] = true;
      return $Validation;
    }

    // 検証成功: セッションに保存して確認画面へ進む
    $_SESSION[$session_key] = [
      'verified'    => true,
      'verified_at' => \time(),
    ];

    return $Validation;
  }

  /**
   * メール送信完了後に verified → sent へ切り替える
   *
   * セッションを即破棄すると完了画面を表示する後続リクエストで
   * フラグが失われ、入力画面へ差し戻されてしまう。
   * verified を落として二重送信を防ぎつつ、sent フラグを残して
   * 完了画面の表示だけを許可する。
   */
  public function afterSend(mixed $Data): void
  {
    // このリクエストではメール送信完了として記録する
    $this->request_state['complete_passed'] = true;

    $filter_key  = \current_filter();
    $session_key = self::SESSION_PREFIX . \md5(
      \str_replace('mwform_after_send_', 'mwform_validation_', $filter_key)
    );

    $_SESSION[$session_key] = [
      'verified' => false,
      'sent'     => true,
      'sent_at'  => \time(),
    ];
  }

  /**
   * 送信済みフラグをリクエスト終了時に消費する
   *
   * 完了画面を一度表示した時点で破棄することで、
   * ブラウザバックによる再送信を遮断する。
   */
  public function consumeSentFlags(): void
  {
    foreach ($this->request_state['sent_replay_keys'] as $session_key) {
      unset($_SESSION[$session_key]);
    }

    $this->request_state['sent_replay_keys'] = [];
  }

  /**
   * MW_WP_Form_Data シングルトンにバリデーションエラーをセットする
   *
   * current_filter() から form_key を抽出して Data::connect() でシングルトンを取得する。
   * clone された $Data（フィルタ第3引数）ではなくシングルトンを使うことで
   * is_valid() の判定に反映される。
   *
   * @param string $message エラーメッセージ
   */
  private function setMwFormValidationError(string $message): void
  {
    // current_filter() = 'mwform_validation_mw-wp-form-{id}' から form_key を抽出
    $form_key = (string) \str_replace('mwform_validation_', '', \current_filter());

    if (empty($form_key) || !\class_exists('MW_WP_Form_Data')) {
      return;
    }

    $this->request_state['error_raised'] = true;

    $SharedData = \MW_WP_Form_Data::connect($form_key);
    // フォームに配置した hidden フィールド名に合わせてエラーを登録する
    $SharedData->set_validation_error('turnstile-check', 'turnstile', $message);
  }

  /**
   * mwform_redirect_url_ フィルタ
   *
   * このリクエストでエラーを発生させた場合かつ error_flash が立っていれば
   * 入力ページへリダイレクトさせる。
   * verifyAndSaveSession で MW WP Form のバリデーションエラーをセットするため
   * MW WP Form は自力で入力ページに戻るが、万一 URL が確認ページになった場合の保険。
   *
   * @param string $url  MW WP Form が決定したリダイレクト先 URL
   * @param mixed  $Data MW WP Form Data オブジェクト
   */
  public function filterRedirectUrl(string $url, mixed $Data): string
  {
    if (empty($this->request_state['error_raised'])) {
      return $url;
    }

    if (empty($_SESSION['turnstile_error_flash'])) {
      return $url;
    }

    foreach ($this->resolveMwFormIds() as $page_id => $form_id) {
      if (\is_page($page_id)) {
        return \get_permalink($page_id) ?: $url;
      }
    }

    return $url;
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
   * バリデーションをすり抜けた場合の最終防衛ライン
   *
   * complete_passed フラグ（verified 経由の正規通過）がなければ
   * メール宛先を空にして送信を無効化する。
   * sent 経由の通過（完了画面の再表示）では complete_passed が立たないためここで止まる。
   *
   * @param mixed $Mail   MW WP Form Mail オブジェクト
   * @param mixed $values フォームデータ
   * @param mixed $Data   MW WP Form Data オブジェクト
   */
  public function blockMail(mixed $Mail, mixed $values, mixed $Data): mixed
  {
    if ($this->request_state['complete_passed']) {
      return $Mail;
    }

    $Mail->to  = '';
    $Mail->cc  = '';
    $Mail->bcc = '';

    $_SESSION['turnstile_error_flash'] = true;

    return $Mail;
  }

  /**
   * 入力ページ上部にエラーメッセージを表示する
   *
   * セッションのフラッシュメッセージを使い、表示後に即削除する。
   *
   * @param string $content 投稿コンテンツ
   */
  public function showBlockedError(string $content): string
  {
    if (empty($_SESSION['turnstile_error_flash'])) {
      return $content;
    }

    unset($_SESSION['turnstile_error_flash']);

    $text    = Config::get('recaptcha.turnstile.messages.no_token') ?? 'スパム対策のチェックを行ってください。';
    $message = '<div class="turnstile-error" style="padding:16px;margin:24px 0;color:#b00;font-weight:bold;background-color:#ffeaea;border:1px solid #b00;">'
      . \esc_html($text) . '</div>';
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

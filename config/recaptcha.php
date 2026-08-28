<?php

/**
 * recaptchaが有料化し、かなり費用がかかるようになったので
 * 代替としてCloudflare Turnstileを使用する予定です。
 */
return [
  'recaptcha' => [
    // 実装しないと思う
  ],

  /**
   * ■Cloudflare Turnstileとは
   * https://www.xserver.ne.jp/bizhp/cloudflare-turnstile/
   *
   * ■実装参考
   * https://lofir.net/mw-wp-form-google-recaptcha-cloudflare-turnstile/
   *
   * app/Plugins/Turnstile.php
   * app/Plugins/MwFormHook.php
   */
  'turnstile' => [

    'sitekey'   => '',
    'secretkey' => '',

    // エラーメッセージ
    'messages'  => [
      'no_token'         => 'スパム対策のチェックを行ってください。',
      'turnstile_failed' => 'ブロックされました。もう一度お試しください。',
    ],

    /**
     * 管理画面
     */
    'login'     => [
      // ログインフォームで有効化するかどうか
      'use_add_turnstile' => false,
    ],

    /**
     * MWFORM
     */
    'mwform'    => [
      // MWFORMで有効化するかどうか
      'use_add_turnstile' => true,

      // 固定ページスラッグ => フォームID
      'forms'             => [
        'contact' => 10,
      ],

      // MWFORMのデフォHTMLに挿入する（プラポリチェック直下）
      'html'              => <<<HTML
      <div
        class="cf-turnstile"
        style="
          display: flex;
          justify-content: center;
          margin-top: 2rem;
        "
        data-sitekey="{{sitekey}}"
      >
      </div>
      <div class="u-ta_center">[mwform_hidden name="turnstile-check" value="0"][mwform_error keys="turnstile-check"]</div>
      HTML,
    ],
  ],
];

<?php

return [
  /**
   * app/Plugins/MwFormHook.phpで参照
   */

  /**
   * 管理者メール上書き設定
   *
   * フォームIDをキーに、送信先・件名を上書きできる。
   * switch_field を指定した場合、そのフィールド値で cases を参照し、
   * 一致したキーの設定を適用する（なければ default を使用）。
   *
   * フォームID => [ 設定 ]
   */
  'admin-mail-overrides' => [
    // 1 => [
    //   'switch_field' => 'お問い合わせ先',
    //   'cases'        => [
    //     '本社' => [
    //       'to'      => '',
    //       'cc'      => '',
    //       'bcc'     => '',
    //       'subject' => '',
    //     ],
    //     '大阪' => [
    //       'to'      => '',
    //       'cc'      => '',
    //       'bcc'     => '',
    //       'subject' => '',
    //     ],
    //   ],
    //   'default'      => [
    //     'to'      => '',
    //     'cc'      => '',
    //     'bcc'     => '',
    //     'subject' => '',
    //   ],
    // ],
  ],

  /**
   * フッターに挿入するスクリプト
   */
  'foot-script' => [
    // プラポリチェックボックスのテキスト・リンク先を変更
    'agreement' => [
      'is_page' => [
        'contact',
        // '',
      ],
      'script' => <<<HTML
            <script>
            $(function() {
                $('.c-form__agreement .mwform-checkbox-field-text').html(
                  '「<a href="/privacy" target="_blank" class="u-txt_ul">プライバシーポリシー</a>」に同意する'
                );
            });
            </script>
          HTML,
    ],
    // 確認画面で特定の要素を非表示
    'hidden-item' => [
      'is_page' => [
        'confirm',
        'thanks',
        // '',
        // '',
      ],
      'script' => <<<HTML
        <script type="text/javascript">
        $(function() {
          if ($('.mw_wp_form_confirm, .mw_wp_form_complete').length) {
            $('.c-form__notes').hide();
            $('.c-form__privacy').hide();
            $('.c-form__agreement').hide();
          }
        });
        </script>
      HTML,
    ],
  ],
];

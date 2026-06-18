<?php

return [
  /**
   * app/Plugins/MwFormHook.phpで参照
   */
  'foot-script' => [

    // プラポリチェックボックスのテキスト・リンク先を変更
    'agreement' => [
      'is_page' => ['contact'],
      'scriput' => <<<HTML
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
      'is_page' => ['confirm', 'thanks'],
      'scriput' => <<<HTML
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

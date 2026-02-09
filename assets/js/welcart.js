
/**
 * ウェルカート汎用JS
 */
(function ($) {
  // デモユーザーのアドレス
  demoUserEmail = '';

  /**
   * パスワードの表示非表示切り替え
   */
  $(function () {
    function addPasswordToggle() {
      $('input[type="password"]').each(function () {
        var $field = $(this);

        // 既にトグルボタンが付いているならスキップ
        if ($field.next('.password-toggle').length > 0) {
          return;
        }

        // 親にposition:relativeを付与（絶対配置用）
        $field.parent().css('position', 'relative');

        // トグルボタン追加
        var toggleButton = $('<span class="password-toggle fa fa-eye"></span>');
        $field.after(toggleButton);

        // クリック時の切り替え処理
        toggleButton.on('click', function () {
          var currentType = $field.attr('type');
          $field.attr('type', currentType === 'password' ? 'text' : 'password');
          $(this).toggleClass('fa-eye fa-eye-slash');
        });

        // キーボード対応（Enter / Space）
        toggleButton.attr({
          tabindex: '0',
          role: 'button',
          'aria-label': 'パスワード表示切替'
        }).on('keydown', function (e) {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this).click();
          }
        });
      });
    }

    // 初期化
    addPasswordToggle();

    // 動的追加対応
    if (typeof MutationObserver !== 'undefined') {
      var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
          if (mutation.type === 'childList') {
            addPasswordToggle();
          }
        });
      });

      observer.observe(document.body, {
        childList: true,
        subtree: true
      });
    }
  });

  /**
   * デモ会員のパスワードリセット防止
   */
  $(function () {
    $('#wc_lostmemberpassword #loginform input[type="submit"]').on('click', function (e) {
      var mail = $('#loginmail').val().trim();
      if (mail === demoUserEmail) {
        alert('デモ会員はパスワードを変更できません');
        e.preventDefault();
        return false;
      }
    });
  });


  /**
   *
   */
  $(function () {
    $('.submit, .send').each(function () {
      $(this)
        .find('input[type="submit"]:visible, input[type="button"]:visible, .wc-btn:visible')
        .first()
        .addClass('first');
    });
  });


})(jQuery);
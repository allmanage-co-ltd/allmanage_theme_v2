<main class="p-contact">

  <div class="p-kv_under">
    <div class="p-kv_under__inner">
      <div class="c-inner">
        <div class="p-kv_under__ttl">
          <div class="en">CONTACT</div>
          <div class="jp">お問い合わせ</div>
        </div>
      </div>
    </div>
  </div>

  <?php the_breadcrumb() ?>

  <section class="l-content -under">
    <!-- <div class="p-contact_head">
      <div class="c-inner">
        <div class="p-contact__inner">
          <div class="p-contact_head__box">
            <h2 class="p-contact_head__ttl">
              テキストテキスト
            </h2>
            <div class="p-contact_head__txt">
              テキストテキストテキストテキストテキストテキスト
            </div>
          </div>
        </div>
      </div>
    </div> -->
    <div id="form" class="p-contact_form">
      <div class="c-inner">
        <div class="p-contact__inner">
          <div class="c-form -thenks">
            <?php
            /**
             * reCAPTCHA for MW WP Formを使用しない場合はdo_shortcodeを使用可能
             */
                        // echo do_shortcode('[mwform_formkey key=""]');

            /**
             * reCAPTCHA for MW WP Formを使用する場合
             *
             * プラグインの仕様で固定ページにショートコードを記載し、the_contentを経由しないければ
             * reCAPTCHAの検証が発火しないため、do_shortcodeは使用できない。
             *
             * 後からreCAPTCHAを追加したい場合に仕様を知らないとドツボにハマる（経験談）ので
             * 基本的には固定ページにショートコードを記載しthe_contentするのを推奨。
             */
            the_content();
            ?>
          </div>
        </div>
      </div>
    </div>
  </section>

</main>

    <?php
    the_layout('footer-contact');
    the_cookie_modal();
    ?>

    <footer class="l-footer">

      <div class="c-inner -lg">
        <div class="l-footer__logo">
          <a href="<?= home(); ?>">
            <img src="<?= img_dir(); ?>/logo.png" alt="<?= get_bloginfo('name'); ?>">
          </a>
        </div>
        <div class="l-footer__inner">

          <div class="l-footer__body">
            <div class="l-footer__naviWrap u-hidden_tb">
              <?php the_layout('footer-navi'); ?>
              <div class="l-footer__copy u-hidden_tb">© Allmanage All Rights Reserved.</div>
            </div>
          </div>

          <div class="l-footer__copy u-visible_tb">© Allmanage All Rights Reserved.</div>
        </div>

    </footer>

    <div id="js-totop" class="c-totop"></div>
    <?php wp_footer(); ?>
    </body>

    </html>
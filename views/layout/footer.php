    <?php
    the_layout('footer-contact');
    //the_cookie_modal();
    ?>

    <footer class="l-footer">

        <div class="c-inner -lg">
            <div class="l-footer__top">
                <div class="l-footer__logo">
                    <a href="<?= home(); ?>">
                        <img src="<?= config('seo.logo_ft') ?>" alt="<?= config('seo.name'); ?>">
                    </a>
                </div>
                <?php the_layout('footer-navi'); ?>
            </div>
            <div class="l-footer__copyright"><?= config('seo.copy') ?></div>
        </div>
    </footer>

    <div id="js-totop" class="c-totop">
        <svg width="12" height="32" viewBox="0 0 12 32" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M11.828 10H6.96899V31.6509H4.85706V10H0L5.91309 0L11.828 10Z" fill="white" />
        </svg>
    </div>
    <?php wp_footer(); ?>
    </body>

    </html>

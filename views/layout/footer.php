    <?php
    the_layout('footer-contact');
    // the_component('totop');
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

    <?php wp_footer(); ?>
    </body>

    </html>

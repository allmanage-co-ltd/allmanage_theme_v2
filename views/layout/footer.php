<?php
if (! is_page(['contact', 'confirm', 'thanks'])) {
    the_layout('footer-contact');
}
?>

<?php
the_component('totop');
// the_cookie_modal(30, url('privacy'));
?>

<footer class="l-footer">
    <div class="c-inner">
        <div class="l-footer__inner">
            <div class="l-footer__info">
                <div class="l-footer__logo">
                    <a href="<?= home(); ?>">
                        <img src="<?= config('seo.logo_ft') ?>" alt="<?= config('seo.name'); ?>">
                        <!-- <img src="<?= config('seo.logo_white') ?>" alt="<?= config('seo.name'); ?>" class="logo_white"> -->
                    </a>
                </div>
                <div class="l-footer__address">〒550-0014<br>大阪市西区北堀江2-2-7　北堀江GATEビル5F</div>
                <div class="l-footer__tel">
                    <a href="tel:00000000000" class="num">000-0000-0000</a>
                    <div class="txt">平日 10:00-18:00<br>土日祝休</div>
                </div>
            </div>
            <?php the_layout('footer-navi'); ?>
        </div>
        <div class="l-footer__copyright"><?= config('seo.copy') ?></div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>

</html>

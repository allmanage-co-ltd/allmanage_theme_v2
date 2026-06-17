<nav class="l-header__navi">
    <div id="js-drawerMenu" class="slideout-menu slideout-menu-left">

        <div class="l-gnavi">
            <div class="l-gnavi__wrap">
                <div class="l-gnavi__logo u-visible_tb">
                    <img src="<?= config('seo.logo') ?>" alt="<?= config('seo.name'); ?>">
                </div>
                <div class="l-gnavi__navi">
                    <?php
                    // the_component('translate');
                    ?>
                    <ul class="l-gnavi__list">
                        <li class="l-gnavi__item ">
                            <a href="<?= url('home') ?>" class="l-gnavi__link">トップ</a>
                        </li>
                        <li class="l-gnavi__item">
                            <a href="<?= url('news') ?>" class="l-gnavi__link">新着情報</a>
                        </li>
                    </ul>
                    <div class="l-gnavi__btn">
                        <a href="<?= url('contact') ?>" class="">
                            ご相談・お問い合わせ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <button type="button" class="l-header__hamburger slideout-hamburger so_toggle u-hidden_pc" id="js-drawerToggle">
        <span></span>
    </button>
</nav>

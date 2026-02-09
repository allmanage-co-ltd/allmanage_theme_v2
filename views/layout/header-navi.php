<nav class="l-header__navi">
  <div id="js-drawerMenu" class="slideout-menu slideout-menu-left">

    <div class="l-gnavi">
      <div class="l-gnavi__logo u-visible_tb">
        <img src="<?= img_dir(); ?>/logo.png" alt="<?= get_bloginfo('name'); ?>">
      </div>
      <div class="l-gnavi__navi">

        <?php the_component('translate') ?>

        <nav class="l-gnavi__menu">
          <ul class="l-gnavi__list">
            <li class="l-gnavi__item">
              <a href="<?= url('home') ?>" class="l-gnavi__link">トップ</a>
            </li>
            <li class="l-gnavi__item">
              <a href="<?= url('news') ?>" class="l-gnavi__link">NEWS</a>
            </li>
            <li class="l-gnavi__item">
              <a href="<?= url('contact') ?>" class="l-gnavi__link">CONTACT</a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>

  <button type="button" class="l-header__hamburger slideout-hamburger so_toggle u-hidden_pc" id="js-drawerToggle">
    <span></span>
  </button>
</nav>
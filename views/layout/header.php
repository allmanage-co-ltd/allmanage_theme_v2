<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
  <?php wp_head(); ?>
</head>

<body class="<?= esc_attr(implode(' ', get_body_class())) ?>">
  <?php if (file_exists(img_dir() . '/symbol-defs.svg')) {
    include_once(img_dir() . '/symbol-defs.svg');
  } ?>

  <?php the_component('image_modal') ?>

  <header class="l-header <?= is_front_page() ? '-page' : ''; ?>" id="js-header">
    <div class="l-header__inner">
      <?php if (is_home() || is_front_page()): ?>
      <h1 class="l-header__logo">
        <a href="<?= url('home'); ?>">
          <img src="<?= img_dir(); ?>/logo.png" alt="<?= get_bloginfo('name'); ?>">
        </a>
      </h1>
      <?php else: ?>
      <div class="l-header__logo">
        <a href="<?= url('home'); ?>">
          <img src="<?= img_dir(); ?>/logo.png" alt="<?= get_bloginfo('name'); ?>">
        </a>
      </div>
      <?php endif; ?>
      <?php the_layout('header-navi') ?>
    </div>
  </header>
  <div class="l-header__overlay slideout-panel slideout-panel-left" id="js-drawerPanel"></div>
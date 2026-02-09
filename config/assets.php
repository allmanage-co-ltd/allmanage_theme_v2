<?php

return [
  /**-----------------------------------
   * 読み込むアセット群
   *----------------------------------*/
  'version'   => '1.0.0',

  // フロント・管理画面で共通。無しの場合はデフォルトが読み込まれる
  'jquery'    => 'https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js',

  // フロントCSS
  'css'       => [
    // 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css',
    'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
    // 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css',
    // 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick-theme.css',
    // 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css',
    // 'https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4',
    theme_uri() . '/assets/css/style.css',
    theme_uri() . '/assets/css/include.css',
    // theme_uri() . '/assets/css/welcart.css',
  ],

  // フロントJS
  'js'        => [
    'https://cdn.jsdelivr.net/npm/flatpickr',
    'https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js',
    // 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js',
    // 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js',
    // 'https://cdn.tailwindcss.com?plugins=typography',
    'https://yubinbango.github.io/yubinbango/yubinbango.js',
    theme_uri() . '/assets/js/scripts.js',
    theme_uri() . '/assets/js/scripts_add.js',
    // theme_uri() . '/assets/js/welcart.js',
  ],

  // 管理画面CSS
  'admin-css' => [
    // 'https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4',
    theme_uri() . '/assets/css/admin.css',
  ],

  // 管理画面JS
  'admin-js'  => [
    // 'https://cdn.tailwindcss.com?plugins=typography',
    theme_uri() . '/assets/js/admin.js',
  ],

];
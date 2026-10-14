<?php

/**
 * app/Presenters/Metadata.phpで参照し、
 * app/Hooks/AddHeadMetadata.phpでフック登録
 */
return [
  /**
   * All In One SEOを使用するかどうか
   *
   * true   → All In One SEOを使用する場合（デフォルトメタデータを出力しない）
   * false  → テーマデフォルトのメタデータを出力する
   */
  'use_all_in_one_seo' => false,

  'name'               => 'Allmanage',
  'copy'               => 'Copyright © ALLMANAGE Co., Ltd. All Rights Reserved',

  // use_all_in_one_seo: false の際に使用するメタデータ
  'title'              => '',
  'description'        => '',
  'keywords'           => '',
  'ogp'                => img_uri() . '/common/ogp.jpg',

  // ロゴ
  'logo'               => img_uri() . '/common/logo.svg',
  'logo_white'         => img_uri() . '/common/logo_white.svg',
  'logo_ft'            => img_uri() . '/common/logo_ft.svg',

  // ファビコン
  'favicon'            => img_uri() . '/common/favicon.png',

  /**
   * GA4タグ
   *
   * 計測キーのみでOK
   */
  'gtags'              => [
    'allmanage' => [
      // 'G-XXXXXXXX',
    ],
    'forval'    => [
      // 'G-XXXXXXXX'
    ],
  ],

  /**
   * GTM
   *
   * 計測IDのみでOK
   */
  'gtm'                => [
    'allmanage' => [
      // 'GTM-XXXXXXXX',
    ],
    'forval'    => [
      // 'GTM-XXXXXXXX',
    ],
  ],

  /**
   * Microsoft Clarity
   *
   * 計測キーのみでOK
   */
  'clarity'            => [
    'allmanage' => [
      // 'XXXXXXXXXX',
    ],
    'forval'    => [
      // 'XXXXXXXXXX',
    ],
  ],
];

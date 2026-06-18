<?php

return [
  /**
   * app/Hooks/SetupTheme.php > adminNoticeRequiredPlugins() で参照
   *
   * - name: プラグインの名称（公式名称でも任意のものでOK）
   * - slug: プラグインのファイルパス（フォルダ名/ファイル名.php）
   *         記載のファイルが存在するかどうかでインストールされているか確認する
   * - repo: wordpress.org のプラグインスラッグ、記載の文字列で検索する
   *         plugin-install.php?s={repo}&tab=search&type=term
   */
  'musts' => [
    [
      'name' => 'SiteGuard WP Plugin',
      'slug' => 'siteguard/siteguard.php',
      'repo' => 'siteguard',
    ],
    [
      'name' => 'WPvivid Backup Plugin',
      'slug' => 'wpvivid-backuprestore/wpvivid-backuprestore.php',
      'repo' => 'wpvivid-backuprestore',
    ],
    [
      'name' => 'All in One SEO',
      'slug' => 'all-in-one-seo-pack/all_in_one_seo_pack.php',
      'repo' => 'all-in-one-seo-pack',
    ],
    [
      'name' => 'MW WP Form',
      'slug' => 'mw-wp-form/mw-wp-form.php',
      'repo' => 'mw-wp-form',
    ],
    [
      'name' => 'Advanced Custom Fields',
      'slug' => 'advanced-custom-fields/acf.php',
      'repo' => 'advanced-custom-fields',
    ],
    [
      'name' => 'Post Types Order',
      'slug' => 'post-types-order/post-types-order.php',
      'repo' => 'post-types-order',
    ],
    [
      'name' => 'Category Order and Taxonomy Terms Order',
      'slug' => 'taxonomy-terms-order/taxonomy-terms-order.php',
      'repo' => 'taxonomy-terms-order',
    ],
  ]
];

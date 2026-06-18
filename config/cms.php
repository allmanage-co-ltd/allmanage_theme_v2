<?php

return [
  /**
   * app/Hooks/RegisterPostType.phpで参照
   *
   * カスタム投稿タイプの設定
   */
  'post_types'        => [
    'news' => [
      'labels'        => [
        'name'          => 'NEWS',
        'singular_name' => 'news',
      ],
      'public'        => true,
      'has_archive'   => true,
      'menu_position' => 5,
      'show_in_rest'  => true,
      'supports'      => [
        'title',
        // 'excerpt',
        'editor',
        'thumbnail',
        'revisions'
      ],
    ],
  ],

  /**
   * app/Hooks/RegisterTaxonomy.phpで参照
   *
   * カスタムタクソノミーの設定
   */
  'taxonomies'        => [
    'news_cat' => [
      'post_type'    => 'news',
      'label'        => 'カテゴリー',
      'hierarchical' => true,
      'public'       => true,
      'show_ui'      => true,
    ],
  ],

  /**
   * app/Hooks/SavePostTaxonomyRequired.phpで参照
   *
   * 投稿タイプのタクソノミーを選択必須に設定
   * 指定タクソノミーが未選択の場合は下書きに戻してエラーメッセージを表示
   *
   * '指定の投稿タイプ' => [ 'タクソノミー' => 'エラーメッセージ' ]
   */
  'taxonomy_required' => [
    'news' => [
      'news_cat' => 'カテゴリーを選択してください。',
      // 'news_tag' => 'タグを選択してください。',
    ],
    // 'works' => [
    //     'works_cat' => 'カテゴリーを選択してください。',
    // ],
  ],

  /**
   * オプションページのテンプレートファイルをどのフォルダに置くか
   */
  'option_view_dir'   => \App\Helpers\Path::views() . '/app/admin',

  /**
   * app/Hooks/RegisterOptionPage.phpで参照
   *
   * オプションページの設定
   */
  'option_pages'      => [
    'csv-in-expoter' => [
      'show'       => true,
      'page_title' => 'CSV',
      'menu_title' => 'CSV',
      'capability' => 'manage_options',
      'slug'       => 'csv-in-expoter',
      'view'       => 'csv-in-expoter.php',
      /**
       * CSVエクスポート・インポートクラス（csv-in-expoter独自定義）
       *
       * - exporter: ExportCsvAbstract を継承したクラス
       * - importer: ImportCsvAbstract を継承したクラス
       * - Hook とオプションページから参照される
       */
      'exporter'   => [
        \App\Project\ExportNewsCsv::class,
        // \App\Project\ExportWorksCsv::class,
      ],
      'importer'   => [
        \App\Project\ImportNewsCsv::class,
        // \App\Project\ImportWorksCsv::class,
      ],
    ],

    /**
     * 管理画面にmwformのお問い合わせ履歴ページを表示
     */
    'inquiry-history' => [
      'show'       => true,
      'page_title' => 'お問い合わせ履歴',
      'menu_title' => 'お問い合わせ履歴',
      'capability' => 'edit_posts',
      'slug'       => 'inquiry-history',
      'redirect'   => 'edit.php?post_type=mw-wp-form&page=mw-wp-form-save-data',
      'icon'       => 'dashicons-email-alt',
      'position'   => 30,
    ],
  ],
];

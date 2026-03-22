<?php

return [
    /**
     * フックの自動読み込みの設定
     *
     * - BootableWpHookInterface を実装したクラをスキャンして自動登録する
     *
     * cache: true にするとキャッシュを有効にする
     * cache_path: キャッシュファイルの保存先
     */
    'hooks_auto_loader' => [
        'cache'       => true,
        'cache_path'  => \App\Support\Path::storage() . '/cache/app/hooks.php',
    ],

    /**
     * カスタム投稿タイプの設定
     */
    'post_types'      => [

        'news' => [
            'labels'        => [
                'name'          => 'NEWS',
                'singular_name' => 'news',
            ],
            'public'        => true,
            'has_archive'   => true,
            'menu_position' => 5,
            'show_in_rest'  => false,
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
     * カスタムタクソノミーの設定
     */
    'taxonomies'      => [

        'news_cat' => [
            'post_type'    => 'news',
            'label'        => 'カテゴリー',
            'hierarchical' => true,
            'public'       => true,
            'show_ui'      => true,
        ],
    ],

    /**
     * オプションページの設定
     */
    'option_view_dir' => \App\Support\Path::views() . '/app/admin',

    'option_pages'    => [

        'csv-in-expoter' => [
            'show'       => true,
            'page_title' => 'CSV',
            'menu_title' => 'CSV',
            'capability' => 'manage_options',
            'slug'       => 'csv-in-expoter',
            'view'       => 'csv-in-expoter.php',

            /**
             * CSVエクスポートクラス（csv-in-expoter独自定義）
             *
             * - CsvExporterInterface を実装したクラス
             * - Hookとオプションページでから参照される
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
    ],

];

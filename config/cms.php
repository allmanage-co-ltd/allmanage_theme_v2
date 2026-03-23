<?php

return [
    /**
     * app/Hooks/Core/HooksAutoLoader.phpで参照
     *
     * フックの自動読み込みの設定
     * - BootableWpHookInterface を実装したクラをスキャンして自動登録する
     *
     * cache: true にするとキャッシュを有効にする
     * cache_path: キャッシュファイルの保存先
     */
    'hooks_auto_loader' => [
        'cache'      => true,
        'cache_path' => \App\Helpers\Path::storage() . '/cache/app/hooks.php',
    ],

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
     * app/Hooks/RegisterOptionPage.phpで参照
     *
     * オプションページの設定
     */
    'option_view_dir'   => \App\Helpers\Path::views() . '/app/admin',

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
    ],

];

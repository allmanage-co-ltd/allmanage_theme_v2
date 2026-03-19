<?php

return [
    /**
     * カスタム投稿タイプの設定
     */
    'post_types'   => [
        // お知らせ
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
    'taxonomies'   => [
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
    'option_pages' => [
        'csv-in-expoter' => [
            'show'       => true,
            'page_title' => 'CSV',
            'menu_title' => 'CSV',
            'capability' => 'manage_options',
            'slug'       => 'csv-in-expoter',
            'view'       => 'csv-in-expoter.php',
        ],
    ],
];

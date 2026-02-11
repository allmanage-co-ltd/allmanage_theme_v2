<?php

return [
    /**-----------------------------------
     * register_post_typeの設定
     *----------------------------------*/
    'post_types'   => [
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
                'excerpt',
                'editor',
                'thumbnail',
                'revisions'
            ],
        ],
    ],
    /**-----------------------------------
     * register_taxonomyの設定
     *----------------------------------*/
    'taxonomies'   => [
        'news_cat' => [
            'post_type'    => 'news',
            'label'        => 'カテゴリー',
            'hierarchical' => true,
            'public'       => true,
            'show_ui'      => true,
        ],
    ],
    /**-----------------------------------
     * add_menu_pageの設定
     *----------------------------------*/
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
    /**-----------------------------------
     * お客様用管理画面の設定
     *----------------------------------*/
    'client_menu'  => [
        // 見せたくないメニュー
        'hidden'         => [
            'index.php',    // ダッシュボード
            'edit.php',     // 投稿・固定ページ
            'upload.php',   // メディア
            'profile.php',  // プロフィール
        ],
        // 見せたいカスタムメニュー
        'visible'        => [
            'post_type' => [ // edit.php?post_type=
                'news',
                'works',
            ],
            'option'    => [ // /admin.php?page=
                'usc-e-shop/usc-e-shop.php', // ウェルカート
                'usces_orderlist',           // ウェルカート
                'csv-in-expoter',
            ],
        ],
        // その他の表示オプション、非表示はfalse
        'default_option' => [
            'helth'       => false, // サイトヘルスステータス
            'activity'    => false, // アクティビティ
            'quick_press' => false, // クイックドラフト
            'primary'     => false, // WordPressイベントとニュース
            'panel'       => false, // ようこそパネル
            'right_now'   => false, // 概要
            'new-content' => false, // WordPressロゴ/コメント/新規追加
            'notices'     => false, // 更新通知
        ],
    ],
];

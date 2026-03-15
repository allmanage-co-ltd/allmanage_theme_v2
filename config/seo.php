<?php

return [
    /**-----------------------------------
     * 主にheadに入れ込むデータとして使用
     * nameには会社名を入れると何かと使い道があるかも？
     *
     * echo config(seo.name);
     *----------------------------------*/
    'name'               => 'Allmanage',
    'copy'               => 'Copyright © ALLMANAGE Co., Ltd. All Rights Reserved',

    'title'              => 'Allmanageテーマ',
    'description'        => 'Allmanageテーマです',
    'keywords'           => '',

    'logo'               => img_uri() . '/common/logo.svg',
    'logo_white'         => img_uri() . '/common/logo_white.svg',
    'logo_ft'            => img_uri() . '/common/logo_ft.png',

    'favicon'            => img_uri() . '/common/favicon.png',
    'ogp'                => img_uri() . '/common/ogp.jpg',

    'gtags'              => [
        // 'G-XXXXXXXX',
    ],

    /**-----------------------------------
     * All In One SEOを使用するかどうか
     *
     * true   → All In One SEOを使用する場合（デフォルトメタデータを出力しない）
     * false  → テーマデフォルトのメタデータを出力する
     *----------------------------------*/
    'use_all_in_one_seo' => false,
];

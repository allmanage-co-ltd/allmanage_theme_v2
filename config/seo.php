<?php

return [
    /**-----------------------------------
     * 主にheadに入れ込むデータとして使用
     * nameには会社名を入れると何かと使い道があるかも？
     *
     * echo config(seo.name);
     *----------------------------------*/
    'name'        => 'Allmanage',

    'title'       => 'Allmanageテーマ',
    'description' => 'Allmanageテーマです',
    'keywords'    => '', // 今時は意味ないらしいです

    'logo'        => img_dir() . '/common/logo.svg',
    'logo_white'  => img_dir() . '/common/logo_white.svg',
    'logo_ft'     => img_dir() . '/common/logo_ft.png',

    'favicon'     => img_dir() . '/common/favicon.png',
    
    'ogp'         => img_dir() . '/common/ogp.jpg',

    'copy'        => 'Copyright © ALLMANAGE Co., Ltd. All Rights Reserved',

    'gtags'       => [
        // 'G-XXXXXXXX',
    ],

    // 未実装（現状ベタ貼りしてください）
    'gtms'       => [
        // 'G-XXXXXXXX',
    ]
];

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
    'keywords'    => 'allmanage,test',
    'logo'        => img_dir() . '/common/logo.svg',
    'favicon'     => img_dir() . '/common/favicon.png',
    'ogp'         => img_dir() . '/common/ogp.jpg',

    'copy'        => 'Copyright © ALLMANAGE Co., Ltd. All Rights Reserved',

    'gtags'       => [
        'XXXXXXXX',
    ],

    // 未実装（現状ベタ貼りしてください）
    'gtm'         => [
        'XXXXXXXX',
    ]
];
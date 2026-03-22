<?php

return [
    /**
     * ルートディレクトリ
     */
    'root'    => __DIR__ . '/..',

    /**
     *
     */
    'hooks_auto_loader' => [
        'cache'       => true,
        'cache_path'  => \App\Support\Path::storage() . '/framework/cache/hooks.php',
    ],

    /**
     * 実行環境判定
     */
    'runtime' => [
        'local'  => [
            'localhost',
            '127.0.0.1',
            'web-checker',
            '.local',
        ],
        'mobile' => [
            'iPhone',
            'iPod',
            'Android',
            'dream',
            'CUPCAKE',
            'blackberry',
            'webOS',
            'incognito',
            'webmate',
        ],
        'robots' => [
            'Googlebot',
            'bingbot',
            'AhrefsBot',
            'Baiduspider',
            'YandexBot',
            'facebookexternalhit',
            'Hatena',
        ],
    ],
];

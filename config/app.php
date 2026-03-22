<?php

return [
    /**
     * ルートディレクトリ
     */
    'root'    => __DIR__ . '/..',

    /**
     * app/Services/Http/Runtime.phpで参照
     *
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

<?php

return [
    'root'    => __DIR__ . '/..',

    'log'     => '/storage/logs/app.log',

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

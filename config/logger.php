<?php

use App\Enums\LogFieeldEnum;

return [
    /**
     *
     */
    'app'    => [
        'use' => true,
        'dir' => '/storage/logs/app/',
    ],

    /**
     *
     */
    'access' => [
        'use'     => true,
        'dir'     => '/storage/logs/access/',
        'channel' => 'access',
        'hooks'   => [
            'template_redirect',
            'admin_init',
        ],
        'content' => [
            LogFieeldEnum::requestId,
            LogFieeldEnum::ip,
            LogFieeldEnum::xff,
            LogFieeldEnum::method,
            LogFieeldEnum::uri,
            LogFieeldEnum::query,
            LogFieeldEnum::referer,
            LogFieeldEnum::ua,
            LogFieeldEnum::userId,
            LogFieeldEnum::postId,
            LogFieeldEnum::postType,
            LogFieeldEnum::status,
            LogFieeldEnum::is404,
        ],
    ],

    /**
     *
     */
    'error'  => [
        'use'     => true,
        'dir'     => '/storage/logs/errors/',
        'content' => [
            LogFieeldEnum::requestId,
            LogFieeldEnum::ip,
            LogFieeldEnum::ua,
            LogFieeldEnum::xff,
            LogFieeldEnum::status,
            LogFieeldEnum::method,
            LogFieeldEnum::uri,
            LogFieeldEnum::query,
            LogFieeldEnum::referer,
        ],
    ],
];

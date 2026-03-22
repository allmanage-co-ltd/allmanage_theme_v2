<?php

use App\Enums\LogFieldEnum;

return [
    /**
     * アプリケーションログ設定
     */
    'app'    => [
        'use' => true,
        'dir' => '/storage/logs/app/',
    ],

    /**
     * アクセスログ設定
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
            LogFieldEnum::RequestId,
            LogFieldEnum::Ip,
            LogFieldEnum::Xff,
            LogFieldEnum::Method,
            LogFieldEnum::Uri,
            LogFieldEnum::Query,
            LogFieldEnum::Referer,
            LogFieldEnum::Ua,
            LogFieldEnum::UserId,
            LogFieldEnum::PostId,
            LogFieldEnum::PostType,
            LogFieldEnum::Status,
            LogFieldEnum::Is404,
        ],
    ],

    /**
     * エラーログ設定
     */
    'error'  => [
        'use'     => true,
        'dir'     => '/storage/logs/errors/',
        'content' => [
            LogFieldEnum::RequestId,
            LogFieldEnum::Ip,
            LogFieldEnum::Ua,
            LogFieldEnum::Xff,
            LogFieldEnum::Status,
            LogFieldEnum::Method,
            LogFieldEnum::Uri,
            LogFieldEnum::Query,
            LogFieldEnum::Referer,
        ],
    ],
];

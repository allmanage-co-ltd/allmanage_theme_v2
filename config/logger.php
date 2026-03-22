<?php

/**
 * 以下で参照
 * - app/Services/Logger/Logger.php
 * - app/Hooks/AccessLog.php
 *
 * contentのEnum定義は以下で設定値に基づく値を解決している
 *  - app/Services/Logger/LogFieldResolver.php
 */
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
            \App\Enums\LogFieldEnum::RequestId,
            \App\Enums\LogFieldEnum::Ip,
            \App\Enums\LogFieldEnum::Xff,
            \App\Enums\LogFieldEnum::Method,
            \App\Enums\LogFieldEnum::Uri,
            \App\Enums\LogFieldEnum::Query,
            \App\Enums\LogFieldEnum::Referer,
            \App\Enums\LogFieldEnum::Ua,
            \App\Enums\LogFieldEnum::UserId,
            \App\Enums\LogFieldEnum::PostId,
            \App\Enums\LogFieldEnum::PostType,
            \App\Enums\LogFieldEnum::Status,
            \App\Enums\LogFieldEnum::Is404,
        ],
    ],

    /**
     * エラーログ設定
     */
    'error'  => [
        'use'     => true,
        'dir'     => '/storage/logs/errors/',
        'content' => [
            \App\Enums\LogFieldEnum::RequestId,
            \App\Enums\LogFieldEnum::Ip,
            \App\Enums\LogFieldEnum::Ua,
            \App\Enums\LogFieldEnum::Xff,
            \App\Enums\LogFieldEnum::Status,
            \App\Enums\LogFieldEnum::Method,
            \App\Enums\LogFieldEnum::Uri,
            \App\Enums\LogFieldEnum::Query,
            \App\Enums\LogFieldEnum::Referer,
        ],
    ],
];

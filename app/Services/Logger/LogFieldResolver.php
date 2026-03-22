<?php

namespace App\Actions\Logger;

use App\Enums\LogFieeldEnum;
use App\Support\Runtime;

class LogFieldResolver
{
    public static function handle(array $fields): array
    {
        $map = [
            LogFieeldEnum::RequestId->value => fn() => Runtime::requestId(),
            LogFieeldEnum::Ip->value        => fn() => $_SERVER['REMOTE_ADDR'] ?? '',
            LogFieeldEnum::Xff->value       => fn() => explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '')[0] ?? '',
            LogFieeldEnum::Method->value    => fn() => $_SERVER['REQUEST_METHOD'] ?? '',
            LogFieeldEnum::Uri->value       => fn() => $_SERVER['REQUEST_URI'] ?? '',
            LogFieeldEnum::Query->value     => fn() => $_SERVER['QUERY_STRING'] ?? '',
            LogFieeldEnum::Referer->value   => fn() => $_SERVER['HTTP_REFERER'] ?? '',
            LogFieeldEnum::Ua->value        => fn() => $_SERVER['HTTP_USER_AGENT'] ?? '',
            LogFieeldEnum::UserId->value    => fn() => \get_current_user_id(),
            LogFieeldEnum::PostId->value    => fn() => \get_queried_object_id(),
            LogFieeldEnum::PostType->value  => fn() => \get_post_type(\get_queried_object_id()),
            LogFieeldEnum::Status->value    => fn() => \http_response_code(),
            LogFieeldEnum::Is404->value     => fn() => \is_404(),
        ];

        $result = [];

        foreach ($fields as $field) {
            $key = $field->value;

            if (isset($map[$key])) {
                $result[$key] = $map[$key]();
            }
        }
        return $result;
    }
}

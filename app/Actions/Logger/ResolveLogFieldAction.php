<?php

namespace App\Actions\Logger;

use App\Enums\LogFieeldEnum;
use App\Support\Runtime;

class ResolveLogFieldAction
{
    public static function handle(array $fields): array
    {
        $map = [
            LogFieeldEnum::requestId->value => fn() => Runtime::requestId(),
            LogFieeldEnum::ip->value        => fn() => $_SERVER['REMOTE_ADDR'] ?? '',
            LogFieeldEnum::xff->value       => fn() => explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '')[0] ?? '',
            LogFieeldEnum::method->value    => fn() => $_SERVER['REQUEST_METHOD'] ?? '',
            LogFieeldEnum::uri->value       => fn() => $_SERVER['REQUEST_URI'] ?? '',
            LogFieeldEnum::query->value     => fn() => $_SERVER['QUERY_STRING'] ?? '',
            LogFieeldEnum::referer->value   => fn() => $_SERVER['HTTP_REFERER'] ?? '',
            LogFieeldEnum::ua->value        => fn() => $_SERVER['HTTP_USER_AGENT'] ?? '',
            LogFieeldEnum::userId->value    => fn() => \get_current_user_id(),
            LogFieeldEnum::postId->value    => fn() => \get_queried_object_id(),
            LogFieeldEnum::postType->value  => fn() => \get_post_type(\get_queried_object_id()),
            LogFieeldEnum::status->value    => fn() => \http_response_code(),
            LogFieeldEnum::is404->value     => fn() => \is_404(),
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

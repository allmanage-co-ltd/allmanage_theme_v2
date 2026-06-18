<?php

namespace App\Services\Logger;

use App\Enums\LogFieldEnum;
use App\Helpers\Arr;
use App\Services\Http\Runtime;

/**
 * ログに付与する共通フィールド解決
 */
class LogFieldResolver
{
  public static function handle(array $fields): array
  {
    $map = [
      LogFieldEnum::RequestId->value => fn() => Runtime::requestId(),
      LogFieldEnum::Ip->value        => fn() => $_SERVER['REMOTE_ADDR'] ?? '',
      LogFieldEnum::Xff->value       => fn() => Arr::first(Arr::split($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '')) ?? '',
      LogFieldEnum::Method->value    => fn() => $_SERVER['REQUEST_METHOD'] ?? '',
      LogFieldEnum::Uri->value       => fn() => $_SERVER['REQUEST_URI'] ?? '',
      LogFieldEnum::Query->value     => fn() => $_SERVER['QUERY_STRING'] ?? '',
      LogFieldEnum::Referer->value   => fn() => $_SERVER['HTTP_REFERER'] ?? '',
      LogFieldEnum::Ua->value        => fn() => $_SERVER['HTTP_USER_AGENT'] ?? '',
      LogFieldEnum::UserId->value    => fn() => \get_current_user_id(),
      LogFieldEnum::PostId->value    => fn() => \get_queried_object_id(),
      LogFieldEnum::PostType->value  => fn() => \get_post_type(\get_queried_object_id()),
      LogFieldEnum::Status->value    => fn() => \http_response_code(),
      LogFieldEnum::Is404->value     => fn() => \is_404(),
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

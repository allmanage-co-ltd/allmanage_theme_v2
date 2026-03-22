<?php

namespace App\Enums;

enum LogFieeldEnum: string
{
    case RequestId = 'request_id';
    case Ip        = 'ip';
    case Xff       = 'xff';
    case Method    = 'method';
    case Uri       = 'uri';
    case Query     = 'query';
    case Referer   = 'referer';
    case Ua        = 'ua';
    case UserId    = 'user_id';
    case PostId    = 'post_id';
    case PostType  = 'post_type';
    case Status    = 'status';
    case Is404     = 'is_404';
}

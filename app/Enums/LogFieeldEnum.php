<?php

namespace App\Enums;

enum LogFieeldEnum: string
{
    case requestId = 'request_id';
    case ip        = 'ip';
    case xff       = 'xff';
    case method    = 'method';
    case uri       = 'uri';
    case query     = 'query';
    case referer   = 'referer';
    case ua        = 'ua';
    case userId    = 'user_id';
    case postId    = 'post_id';
    case postType  = 'post_type';
    case status    = 'status';
    case is404     = 'is_404';
}

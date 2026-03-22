<?php

namespace App\Enums;

enum CsvColumnEnum: string
{
    case PostId        = 'post_id';
    case PostStatus    = 'post_status';
    case PostTitle     = 'post_title';
    case PostContent   = 'post_content';
    case PostDate      = 'post_date';
    case PostThumbnail = 'post_thumbnail';
}

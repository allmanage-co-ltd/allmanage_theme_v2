<?php

namespace App\Packages\Csv\Enums;

enum ImportColumnActionEnum: string
{
    case SavePost     = 'save_post';
    case UpdateMeta   = 'update_meta';
    case SetTerms     = 'set_terms';
    case SetThumbnail = 'set_thumbnail';
}

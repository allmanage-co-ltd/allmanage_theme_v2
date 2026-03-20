<?php

namespace App\Enums;

enum CsvColumnActionEnum: string
{
    case SavePost     = 'save_post';
    case UpdateMeta   = 'update_meta';
    case SetTerms     = 'set_terms';
    case SetThumbnail = 'set_thumbnail';
}

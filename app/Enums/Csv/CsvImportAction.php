<?php

namespace App\Enums\Csv;

enum CsvImportAction: string
{
    case SAVE_POST     = 'save_post';
    case UPDATE_META   = 'update_meta';
    case SET_TERMS     = 'set_terms';
    case SET_THUMBNAIL = 'set_thumbnail';
}

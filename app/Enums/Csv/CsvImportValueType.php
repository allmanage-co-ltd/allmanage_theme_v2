<?php

namespace App\Enums\Csv;

enum CsvImportValueType: string
{
    case TEXT    = 'text';
    case BOOL    = 'bool';
    case ARRAY   = 'array';
    case GALLERY = 'gallery';
}

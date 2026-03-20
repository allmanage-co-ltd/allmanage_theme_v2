<?php

namespace App\WordPress\Csv\Enums;

enum CsvImportValueTypeEnum: string
{
    case Text    = 'text';
    case Bool    = 'bool';
    case Array   = 'array';
    case Gallery = 'gallery';
}

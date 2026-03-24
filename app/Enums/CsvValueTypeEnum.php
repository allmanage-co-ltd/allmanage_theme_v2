<?php

namespace App\Enums;

enum CsvValueTypeEnum: string
{
    case Text    = 'text';
    case Bool    = 'bool';
    case Array   = 'array';
    case Gallery = 'gallery';
}

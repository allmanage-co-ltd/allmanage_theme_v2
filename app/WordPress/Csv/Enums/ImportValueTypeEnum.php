<?php

namespace App\WordPress\Csv\Enums;

enum ImportValueTypeEnum: string
{
    case Text    = 'text';
    case Bool    = 'bool';
    case Array   = 'array';
    case Gallery = 'gallery';
}

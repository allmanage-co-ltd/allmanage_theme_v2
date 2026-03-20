<?php

namespace App\Packages\Csv\Enums;

enum ImportValueTypeEnum: string
{
    case Text    = 'text';
    case Bool    = 'bool';
    case Array   = 'array';
    case Gallery = 'gallery';
}

<?php

namespace App\Helpers;

use App\Services\Config;

/**---------------------------------------------
 *
 * ---------------------------------------------
 */
class Path
{
    // ルートパス
    public static function root(): string
    {
        return realpath(dirname(__DIR__, 2));
    }

    // appパス
    public static function app(string $path = ''): string
    {
        return self::join(self::root(), 'app', $path);
    }

    // configパス
    public static function config(string $path = ''): string
    {
        return self::join(self::root(), 'config', $path);
    }

    // viewsパス
    public static function views(string $path = ''): string
    {
        return self::join(self::root(), 'views', $path);
    }

    // パス結合
    private static function join(string ...$parts): string
    {
        $first = array_shift($parts);

        return rtrim($first, '/')
            . '/'
            . implode('/', array_map(
                fn($p) => trim($p, '/'),
                $parts
            ));
    }

}

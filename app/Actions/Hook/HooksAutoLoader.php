<?php

namespace App\Actions\Hook;

use App\Support\Config;
use App\Support\Path;
use App\Interfaces\BootableWpHookInterface;

class HooksAutoLoader
{
    public static function handle(): void
    {
        $config = Config::get('app.hooks_auto_loader');

        $useCache = is_array($config) ? ($config['cache'] ?? false) : false;

        $classes = $useCache
            ? self::loadFromCache()
            : self::scan();

        foreach ($classes as $class) {

            if (!class_exists($class)) {
                continue;
            }

            $ref = new \ReflectionClass($class);

            if ($ref->isAbstract()) {
                continue;
            }

            if (!$ref->implementsInterface(BootableWpHookInterface::class)) {
                continue;
            }

            $instance = $ref->newInstance();
            $instance->boot();
        }
    }

    private static function loadFromCache(): array
    {
        $path = self::cachePath();

        if (!file_exists($path) || self::isChanged($path)) {
            $classes = self::scan();

            self::writeCache($path, $classes);
            $written = self::writeCache($path, $classes);

            if ($written) {
                self::writeHash($path);
            }

            return $classes;
        }

        try {
            $data = require $path;
        } catch (\Throwable $e) {
            return self::scan();
        }

        return is_array($data) ? $data : self::scan();
    }

    private static function isChanged(string $cachePath): bool
    {
        $currentHash = self::calculateHash();

        $hashFile = $cachePath . '.hash';

        $oldHash = file_exists($hashFile)
            ? file_get_contents($hashFile)
            : null;

        return $currentHash !== $oldHash;
    }

    private static function calculateHash(): string
    {
        $baseDir = rtrim(Path::app(), '/');

        $hash = '';

        foreach (
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($baseDir, \FilesystemIterator::SKIP_DOTS)
            ) as $file
        ) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $hash .= $file->getPathname() . $file->getMTime();
        }

        return md5($hash);
    }

    private static function writeHash(string $cachePath): void
    {
        $hashFile = $cachePath . '.hash';
        file_put_contents($hashFile, self::calculateHash());
    }

    private static function writeCache(string $path, array $classes): bool
    {
        if (file_exists($path)) {
            @unlink($path);
        }

        $dir = dirname($path);

        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        if (!is_writable($dir)) {
            return false;
        }

        $result = @file_put_contents(
            $path,
            '<?php return ' . var_export($classes, true) . ';'
        );

        return $result !== false;
    }

    private static function scan(): array
    {
        $baseDir = rtrim(Path::app(), '/');
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $baseDir,
                \FilesystemIterator::SKIP_DOTS
            )
        );

        $classes = [];

        foreach ($iterator as $file) {

            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $class = self::fileToClass($file->getPathname());

            if (!class_exists($class)) {
                continue;
            }

            if (is_subclass_of($class, BootableWpHookInterface::class)) {
                $classes[] = $class;
            }
        }

        return $classes;
    }

    private static function cachePath(): string
    {
        return Path::storage() . '/cache/app/hooks.php';
    }

    private static function fileToClass(string $file): string
    {
        $base = rtrim(Path::app(), '/');

        $real = realpath($file);

        if (!$real) {
            return '';
        }

        $relative = ltrim(str_replace($base, '', $real), '/');

        $relative = str_replace(['/', '.php'], ['\\', ''], $relative);

        return 'App\\' . $relative;
    }
}

<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
  ->withPaths([
    __DIR__ . '/',
  ])
  ->withSkip([
    __DIR__ . '/vendor',
    __DIR__ . '/tests',
    __DIR__ . '/docker',
    __DIR__ . '/assets',
    __DIR__ . '/.vscode',
    __DIR__ . '/.github',
    __DIR__ . '/.git',
  ])
  ->withPhpVersion(PhpVersion::PHP_82)
  ->withSets([
    SetList::PHP_80,
    SetList::PHP_81,
    SetList::PHP_82,
    SetList::PHP_83,
  ])
  ->withPHPStanConfigs([
    __DIR__ . '/phpstan.neon',
  ])
  ->withCache(__DIR__ . '/storage/cache/rector/cache')
  ->withTypeCoverageLevel(0)
  ->withDeadCodeLevel(0)
  ->withCodeQualityLevel(0);

/**
 * PHPバージョンダウン時は↑をコメントアウトし、下記をコメント解除
 */
// use Rector\Config\RectorConfig;
// use Rector\Set\ValueObject\DowngradeSetList;
// use Rector\ValueObject\PhpVersion;

// return RectorConfig::configure()
//   ->withPaths([
//     __DIR__ . '/',
//   ])
//   ->withSkip([
//     __DIR__ . '/vendor',
//     __DIR__ . '/tests',
//     __DIR__ . '/docker',
//     __DIR__ . '/assets',
//     __DIR__ . '/.vscode',
//     __DIR__ . '/.github',
//     __DIR__ . '/.git',
//   ])
//   // 目標バージョンを7.4に設定
//   ->withPhpVersion(PhpVersion::PHP_74)
//   // アップグレードではなくダウングレードセットを使う
//   ->withSets([
//     DowngradeSetList::PHP_80,
//     DowngradeSetList::PHP_81,
//     DowngradeSetList::PHP_82,
//     DowngradeSetList::PHP_83,
//   ])
//   ->withPHPStanConfigs([
//     __DIR__ . '/phpstan.neon',
//   ])
//   ->withCache(__DIR__ . '/storage/cache/rector/cache')
//   ->withTypeCoverageLevel(0)
//   ->withDeadCodeLevel(0)
//   ->withCodeQualityLevel(0);

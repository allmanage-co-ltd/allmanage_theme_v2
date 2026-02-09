<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $config): void {

  // ここでPHPバージョンに合わせてセット
  $config->sets([
    LevelSetList::UP_TO_PHP_74,
    LevelSetList::UP_TO_PHP_80,
    LevelSetList::UP_TO_PHP_81,
    LevelSetList::UP_TO_PHP_82,
    LevelSetList::UP_TO_PHP_83,
  ]);

  // 対象ディレクトリ
  $config->paths([
    __DIR__ . '/',
  ]);

  // 除外ディレクトリ
  $config->skip([
    __DIR__ . '/vender',
    __DIR__ . '/style',
    __DIR__ . '/img',
    __DIR__ . '/js',
    __DIR__ . '/.vscode',
    __DIR__ . '/.github',
    __DIR__ . '/.git',
  ]);
};

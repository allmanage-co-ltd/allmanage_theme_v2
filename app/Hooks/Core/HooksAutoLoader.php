<?php

namespace App\Hooks\Core;

use App\Services\Config;
use App\Helpers\Path;
use App\Interfaces\BootableWpHookInterface;

/**
 * Hook自動読み込みクラス
 *
 * ---------------------------------------------
 * 役割
 * ---------------------------------------------
 * - app配下のPHPクラスを走査する
 * - BootableWpHookInterface を実装したクラスだけを抽出する
 * - 抽出したHookクラスをnewして boot() を実行する
 *
 * ---------------------------------------------
 * キャッシュについて
 * ---------------------------------------------
 * - configで cache=true の場合はキャッシュファイルを利用する
 * - キャッシュが存在しない、または app配下のPHPファイルに変更があれば再生成する
 * - 変更判定は「ファイルパス + 更新時刻」からハッシュを生成して比較する
 *
 * ---------------------------------------------
 * 前提
 * ---------------------------------------------
 * - Hookクラスは PSR-4 に従って App\ 以下へ配置する
 * - Hookクラスは BootableWpHookInterface を実装する
 * - 抽象クラスは自動起動しない
 */
class HooksAutoLoader
{
  /**
   * Hook自動読み込みの実行入口
   *
   * 処理の流れ:
   * 1. configからキャッシュ利用設定を取得
   * 2. キャッシュ有効ならキャッシュ読み込み、無効なら都度スキャン
   * 3. クラスごとに存在確認・抽象判定・Interface実装判定を行う
   * 4. 条件を満たすクラスだけ new して boot() を呼ぶ
   */
  public static function handle(): void
  {
    $config = Config::get('app.hooks_auto_loader');

    // hooks_auto_loader が配列で定義されている場合のみ cache 設定を読む
    // 未設定時は安全側として false 扱いにする
    $useCache = \is_array($config) ? ($config['cache'] ?? false) : false;

    // キャッシュ利用時はキャッシュ経由でクラス一覧を取得
    // 未使用時は毎回ファイルスキャンする
    $classes = $useCache
      ? self::loadFromCache()
      : self::scan();

    foreach ($classes as $class) {

      // クラスが存在しない場合はスキップ
      // fileToClass() で推測したクラス名がPSR-4と一致しない場合などに備える
      if (!\class_exists($class)) {
        continue;
      }

      (new $class())->boot();
    }
  }

  /**
   * キャッシュからクラス一覧を取得する
   *
   * 挙動:
   * - キャッシュファイルが存在しない場合は再スキャンして生成する
   * - ハッシュ比較で変更が検知された場合は再スキャンして上書きする
   * - キャッシュ require 時に壊れていたら安全側で再スキャンする
   */
  private static function loadFromCache(): array
  {
    $path = self::cachePath();

    // キャッシュ未作成、または app配下に変更が入った場合は再生成
    if (!\file_exists($path) || self::isChanged($path)) {
      $classes = self::scan();

      // キャッシュ書き込み
      $written = self::writeCache($path, $classes);

      // キャッシュファイル書き込みに成功した場合のみハッシュも更新
      if ($written) {
        self::writeHash($path);
      }

      return $classes;
    }

    try {
      // キャッシュファイルは
      // <?php return [...];
      // の形式を想定している
      $data = require $path;
    } catch (\Throwable $e) {
      // キャッシュ破損時は安全に再スキャンへフォールバック
      return self::scan();
    }

    // 読み込めた値が配列なら採用
    // 不正値なら再スキャンへフォールバック
    return \is_array($data) ? $data : self::scan();
  }

  /**
   * app配下のPHPファイルに変更があるか判定する
   *
   * 判定方法:
   * - 現在の app配下のファイルハッシュを計算
   * - キャッシュ作成時の .hash ファイルと比較
   * - 異なっていれば変更ありとみなす
   */
  private static function isChanged(string $cachePath): bool
  {
    $currentHash = self::calculateHash();

    $hashFile = $cachePath . '.hash';

    $oldHash = \file_exists($hashFile)
      ? \file_get_contents($hashFile)
      : null;

    return $currentHash !== $oldHash;
  }

  /**
   * app配下のPHPファイル群から変更検知用ハッシュを生成する
   *
   * ハッシュ対象:
   * - ファイルの絶対パス
   * - 更新時刻
   *
   * 補足:
   * - 内容そのものではなく mtime ベースのため高速
   * - リネームや更新時刻変化を検知できる
   * - ただしmtime依存なので、特殊なデプロイ運用では注意が必要
   */
  private static function calculateHash(): string
  {
    $baseDir = \rtrim(Path::app(), '/');

    $hash = '';

    foreach (
      new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($baseDir, \FilesystemIterator::SKIP_DOTS)
      ) as $file
    ) {
      // PHPファイル以外は無視
      if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
      }

      $hash .= $file->getPathname() . $file->getMTime();
    }

    return \md5($hash);
  }

  /**
   * 現在の app配下状態を表すハッシュを .hash ファイルへ保存する
   *
   * キャッシュ本体と対になるメタ情報として扱う
   */
  private static function writeHash(string $cachePath): void
  {
    $hashFile = $cachePath . '.hash';
    \file_put_contents($hashFile, self::calculateHash());
  }

  /**
   * クラス一覧をキャッシュファイルへ保存する
   *
   * 保存形式:
   * - PHPファイルとして保存し、require で即読み込みできる形にする
   *
   * 例:
   * <?php return ['App\\Hooks\\ExampleHook'];
   */
  private static function writeCache(string $path, array $classes): bool
  {
    // 古いキャッシュがある場合は削除してから再作成する
    if (\file_exists($path)) {
      @\unlink($path);
    }

    $dir = \dirname($path);

    // ディレクトリが存在しない場合は再帰的に作成
    if (!\is_dir($dir)) {
      @\mkdir($dir, 0777, true);
    }

    // 書き込みできない場合は失敗扱い
    if (!\is_writable($dir)) {
      return false;
    }

    $result = @\file_put_contents(
      $path,
      '<?php return ' . \var_export($classes, true) . ';'
    );

    return $result !== false;
  }

  /**
   * app配下を再帰的に走査し、Hook対象クラス一覧を収集する
   *
   * 抽出条件:
   * - PHPファイルである
   * - App\ から始まるクラス名に変換できる
   * - class_exists() が true
   * - BootableWpHookInterface の実装クラスである
   */
  private static function scan(): array
  {
    $baseDir = \rtrim(Path::app(), '/');

    $iterator = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator(
        $baseDir,
        \FilesystemIterator::SKIP_DOTS
      )
    );

    $classes = [];

    foreach ($iterator as $file) {

      // PHPファイル以外は対象外
      if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
      }

      // ファイルパスから想定FQCNを組み立てる
      $class = self::fileToClass($file->getPathname());

      // クラス名解決に失敗、またはautoload不可ならスキップ
      if (!\class_exists($class)) {
        continue;
      }

      // Hook起動対象はInterface実装クラスのみ
      $ref = new \ReflectionClass($class);

      if (
        !$ref->isAbstract() &&
        $ref->implementsInterface(BootableWpHookInterface::class)
      ) {
        $classes[] = $class;
      }
    }

    return $classes;
  }

  /**
   * キャッシュファイルパスをconfigから取得する
   *
   * config例:
   * app.hooks_auto_loader.cache_path
   */
  private static function cachePath(): string
  {
    return Config::get('app.hooks_auto_loader.cache_path');
  }

  /**
   * ファイルパスを App\ 配下のクラス名へ変換する
   *
   * 例:
   * /var/www/app/Hooks/SetupTheme.php
   * ↓
   * App\Hooks\SetupTheme
   *
   * 前提:
   * - app ディレクトリが App\ 名前空間に対応している
   * - ディレクトリ構成とnamespaceが一致している
   */
  private static function fileToClass(string $file): string
  {
    $base = \rtrim(Path::app(), '/');

    $real = \realpath($file);

    // realpath 失敗時は空文字を返す
    // 呼び出し側で class_exists('') が false になるため自然に除外される
    if (!$real) {
      return '';
    }

    // app ルートからの相対パスへ変換
    $relative = \ltrim(\str_replace($base, '', $real), '/');

    // パス区切りと拡張子をクラス名形式へ変換
    $relative = \str_replace(['/', '.php'], ['\\', ''], $relative);

    return 'App\\' . $relative;
  }
}

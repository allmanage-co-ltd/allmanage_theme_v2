<?php

declare(strict_types=1);

use App\Bootstrap\App;

/**---------------------------------------------
 * テーマ起動エントリーポイント
 * ---------------------------------------------
 * ワードプレス従来のグローバル関数を書くfunctions.phpは
 * app/functions.phpに記述してください。
 *
 * ※このファイルは最重要なため編集しないでください。
 */

//  Composerへの依存関係を読み込む
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
  require_once __DIR__ . '/vendor/autoload.php';
}

// アプリケーションを起動
(new App())->boot();
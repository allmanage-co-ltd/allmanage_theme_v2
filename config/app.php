<?php

return [
  /**
   * ルートディレクトリ
   */
  'root'    => __DIR__ . '/..',

  /**
   * app/Hooks/Core/HooksAutoLoader.phpで参照
   *
   * フックの自動読み込みの設定
   * - BootableWpHookInterface を実装したクラスをスキャンして自動登録する
   *
   * cache: キャッシュファイルを有効にするか
   * cache_path: キャッシュファイルの保存先
   */
  'hooks_auto_loader' => [
    'cache'      => true,
    'cache_path' => \App\Helpers\Path::storage() . '/cache/app/hooks.php',
  ],

  /**
   * app/Services/Http/Runtime.phpで参照
   *
   * 実行環境判定
   */
  'runtime' => [
    'local'  => [
      'localhost',
      '127.0.0.1',
      'web-checker',
      '.local',
    ],
    'mobile' => [
      'iPhone',
      'iPod',
      'Android',
      'dream',
      'CUPCAKE',
      'blackberry',
      'webOS',
      'incognito',
      'webmate',
    ],
    'robots' => [
      'Googlebot',
      'bingbot',
      'AhrefsBot',
      'Baiduspider',
      'YandexBot',
      'facebookexternalhit',
      'Hatena',
    ],
  ],
];

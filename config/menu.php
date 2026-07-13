<?php

/**
 * app/Hooks/EditClientMenu.phpで参照
 */
return [
  /**
   * お客様用管理画面の設定
   */
  'client_menu' => [

    /**
     * 隠したいメニューを記載
     */
    'hidden'         => [
      'index.php',    // ダッシュボード
      'edit.php',     // 投稿・固定ページ
      'upload.php',   // メディア
      'profile.php',  // プロフィール
    ],

    /**
     * 表示したいメニューを記載
     */
    'visible'        => [
      // edit.php?post_type={post_type}
      // 'post_type' => [
      //   'news',
      //   'works',
      // ],
      'post_type' => array_keys(config('cms.post_types') ?? []),
      // admin.php?page={option}
      'option'    => [
        'usc-e-shop/usc-e-shop.php', // ウェルカート
        'usces_orderlist',           // ウェルカート
        'csv-in-expoter',            // CSVダウンロード
        'inquiry-history',           // お問い合わせ履歴（mw form）
      ],
    ],

    /**
     * その他の表示オプション、非表示はfalse
     */
    'default_option' => [
      'health'      => false, // サイトヘルスステータス
      'activity'    => false, // アクティビティ
      'quick_press' => false, // クイックドラフト
      'primary'     => false, // WordPressイベントとニュース
      'panel'       => false, // ようこそパネル
      'right_now'   => false, // 概要
      'new-content' => false, // WordPressロゴ/コメント/新規追加
      'notices'     => false, // 更新通知
      'aioseo'      => false, // AIOSEOの概要
    ],
  ],
];

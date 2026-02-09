<?php

return [
  /**-----------------------------------
   * CSVインエクスポーターの設定
   *
   * 機能作成中のため使えません
   * 
   *----------------------------------*/
  // オプションページを有効にするかどうか
  'show_admin_menu'    => false,

  // エクスポートするファイル名のプレフィックス
  'export_file_prefix' => 'csv_export_', //+{time}.csv

  // 1行目がヘッダーかどうか
  'header_first_line'  => true,

  // 複数値のあるセルの区切り文字列
  'default_delimiter'  => '|',

  // 処理失敗時にどうするか
  'on_missing'         => 'log', // skip | log | error

  /**-----------------------------------
   * CSVデータ仕様
   * -----------------------------------
   * カラム定義
   * 'column_name' => ['type' => '', 'key' => '']
   *
   * -----------------------------------
   * CSVサンプル
   * -----------------------------------
   * post_title,post_content,news_cat,thumbnail,post_status,price,check,gallery,true_false
   * テスト記事A,本文テキストです,news|release,thumb_a.jpg,publish,1000,a|b,img1.jpg|img2.jpg,1
   * テスト記事B,別の記事内容,news,thumb_b.jpg,draft,2500,b,img3.jpg,0
   * テスト記事C,内容その3,release,,publish,,a|c,,1
   *
   * -----------------------------------
   * 補足
   * -----------------------------------
   * - taxonomy / checkbox / gallery は「|」区切り
   * - thumbnail は ファイル名 または URL
   * - true_false は 1 / 0（true / false も可）
   * - 空欄は未設定として扱う
   *----------------------------------*/
  'data'               => [
    // NEWSカスタム投稿
    'news' => [
      'post_title'   => [
        'type' => 'post',
      ],
      'post_content' => [
        'type' => 'post',
      ],
      'news_cat'     => [
        'type' => 'taxonomy',
      ],
      'thumbnail'    => [
        'type' => 'thumbnail',
      ],
      'post_status'  => [
        'type' => 'post',
      ],
      'price'        => [
        'type' => 'meta',
        'key'  => 'acf_price',
      ],
      'check'        => [
        'type' => 'acf_check',
        'key'  => 'acf_check',
      ],
      'gallery'      => [
        'type' => 'acf_gallery',
        'key'  => 'acf_gallery',
      ],
      'true_false'   => [
        'type' => 'acf_true_false',
        'key'  => 'acf_true_false',
      ],
    ],
  ]

];
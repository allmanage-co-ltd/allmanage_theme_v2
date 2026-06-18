<?php

return [
  /**
   * 投稿タイプ別 ACFフィールド定義
   *
   * - get_acf_fields関数で使用を想定
   *
   * $news_fields = get_acf_fields( get_the_ID() , config('acf.news'));
   * echo $news_fields['acf_price'];
   */
  'news' => [
    'acf_is_public',
    'acf_price',
    'acf_check',
  ],
  'works' => [
    // ...
  ]
];

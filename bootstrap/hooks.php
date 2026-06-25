<?php

declare(strict_types=1);

/**---------------------------------------------
 * WP Hook 仮置き場
 *----------------------------------------------
 * クラス化するほどでもない小さな add_action / add_filter や
 * とりあえず動作確認したいだけ 等の殴り書きを一旦書ける場所
 * ちゃんとテーマに取り入れる際は app/Hooks/ に昇格させること
 */

/**
 * サンプルで消さずに置いています。
 *
 * 50文字以上の記事タイトルを省略
 * 管理画面、投稿詳細ページを除く
 */
// function mb_substr_title(string $title)
// {
//   $limit = 50;
//   $exclusion = is_admin() || is_single() || is_singular();

//   if ($exclusion) return $title;
//   if (mb_strlen($title) > $limit) {
//     return mb_substr($title, 0, $limit) . '[...]';
//   }
//   return $title;
// }
// add_filter('the_title', 'mb_substr_title');

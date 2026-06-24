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
 * サンプル
 */
// add_filter('the_content', function (string $content): string {
//   if (!is_single()) {
//     return $content;
//   }
//   return $content . '<p class="article-footer-note">※この記事は〇〇です</p>';
// });

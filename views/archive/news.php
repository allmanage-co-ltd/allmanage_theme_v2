<?php

$news_query = wpquery_archive('news', 12)->build();

/**
 * 上記の$args中身
 */
// $news_query = [
//   'post_status'    => 'publish',
//   'posts_per_page' => $per_page, // デフォルト10件
//   'paged'          => max(1, get_query_var('paged') ?: get_query_var('page') ?: 1),
//   'post_type'      => $post_type,
//   'orderby'        => 'date',
//   'order'          => 'DESC',
// ];

/**
 * build()の前に追加条件も可能
 * メソッド一覧は → app/Services/Query/MyWpQuery.php
 */
// $news_query = wpquery_archive('news', 12)
//   ->setTaxQuery('news_cat', 'info')
//   ->setMetaRelation('AND')
//   ->setMetaQuery('pickup', 1)
//   ->setMetaQuery('member_only', 1, '!=')
//   ->setOrderByMeta('ranking', 'DESC')
//   ->build();
?>

<main class="p-news -archive">

  <div class="p-kv_under">
    <div class="p-kv_under__inner">
      <div class="c-inner">
        <div class="p-kv_under__ttl">
          <div class="en">NEWS</div>
          <div class="jp">お知らせ</div>
        </div>
      </div>
    </div>
  </div>

  <?php the_breadcrumb() ?>

  <div class="l-content -under">
    <section class="p-news_archive">
      <div class="c-inner">
        <?php the_component('news/c-card_news', ['news_query' => $news_query]); ?>
      </div>
    </section>
  </div>
</main>

<?php
$post_type = $_GET['post_type'] ?? 'news';
$sq    = get_search_query();
$query = wpquery_archive([$post_type], 10)
  ->setSearchQuery($sq)
  ->build();
?>

<main class="p-search -archive">

  <div class="p-kv_under">
    <div class="p-kv_under__inner">
      <div class="c-inner">
        <div class="p-kv_under__ttl">
          <div class="en"><?= get_post_type_object($post_type)->labels->name ?? '検索結果' ?></div>
          <div class="jp">「<?= $sq ?>」の検索結果</div>
        </div>
      </div>
    </div>
  </div>

  <?php the_breadcrumb() ?>

  <div class="l-content -under">
    <section class="p-news_archive">
      <div class="c-inner">
        <?php
        switch ($post_type) {
          case 'news':
            the_component('news/c-card_news', ['news_query' => $query]);
            break;
          // case 'works':
          //     the_component('works/c-card_works', ['works_query' => $query]);
          //     break;
          default:
            the_component('news/c-card_news', ['news_query' => $query]);
            break;
        }
        ?>
      </div>
    </section>
  </div>

</main>

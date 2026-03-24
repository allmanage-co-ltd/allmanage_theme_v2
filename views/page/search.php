<?php
$sq    = get_search_query();
$query = wpquery()
    ->setPostType(['news'])
    ->setSearchQuery($sq)
    ->setPerPage(10)
    ->setOrderByDate()
    ->build();
?>

<main class="p-search -archive">

    <div class="p-kv_under">
        <div class="p-kv_under__inner">
            <div class="c-inner">
                <div class="p-kv_under__ttl">
                    <div class="en">SEARCH</div>
                    <div class="jp">検索結果</div>
                </div>
            </div>
        </div>
    </div>

    <?php the_breadcrumb() ?>

    <div class="l-content -under">
        <section class="p-news_archive">
            <div class="c-inner">
                <?php the_component('news/c-card_news', ['query' => $query]); ?>
            </div>
        </section>
    </div>

</main>

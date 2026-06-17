<?php
$term = get_queried_object();
$taxonomy = $term->taxonomy ?? null;
$query = wpquery()
    ->setPostType('news')
    ->setTaxQuery($taxonomy, $term->term_id, 'term_id')
    ->setPerPage(10)
    ->setOrderByDate()
    ->build();
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
                <?php the_component('news/c-card_news', ['query' => $query]); ?>
            </div>
        </section>
    </div>
</main>

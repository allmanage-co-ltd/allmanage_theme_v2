<?php
$post_id  = get_the_ID();
$post_cat = get_the_terms($post_id, 'news_cat');
$cat_slug = (!is_wp_error($post_cat) && !empty($post_cat)) ? $post_cat[0]->slug : '';
$cat_name = (!is_wp_error($post_cat) && !empty($post_cat)) ? $post_cat[0]->name : 'お知らせ';
$sample_fields = get_acf_fields($post_id, config('acf.news'));
d($sample_fields);
?>

<main class="p-news -single">

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
        <section class="p-news_single">
            <div class="c-inner">
                <div class="p-news_single__head">
                    <time datetime="<?php the_time('Y-m-d H:i:s'); ?>" class="p-news_single__date">
                        <?php the_time('Y.m.d'); ?>
                    </time>
                    <div class="p-news_single__term -<?= $cat_slug ?>">
                        <?= $cat_name ?>
                    </div>
                    <h2 class="p-news_single__ttl">
                        <?php the_title(); ?>
                    </h2>
                </div>
                <div class="p-news_single__body">
                    <div class="wp-editor">
                        <?php the_content(); ?>
                    </div>
                </div>
                <?php the_postnavi(url('news')) ?>
            </div>
        </section>
    </div>
</main>

<?php
$post_id  = get_the_ID();
$news_cat = get_the_terms($post_id, 'news_cat');
$sample_fields = get_acf($post_id)->news();
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

                    <?php if ($news_cat): ?>
                        <div class="p-news_single__term -<?= $news_cat[0]->slug; ?>"><?= $news_cat[0]->name; ?></div>
                    <?php else: ?>
                        <div class="p-news_single__term">お知らせ</div>
                    <?php endif; ?>

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

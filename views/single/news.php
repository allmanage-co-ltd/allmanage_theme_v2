<?php
// $sample = get_acf_action(get_the_ID())->sample();
// d($sample);
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

    <div class="c-inner">
        <?php the_breadcrumb(); ?>
    </div>

    <section class="l-content -under">
        <div class="c-inner">
            <?php the_title() ?>
            <?php the_content() ?>
        </div>
    </section>

</main>
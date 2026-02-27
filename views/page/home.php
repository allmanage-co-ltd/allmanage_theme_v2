<?php
if (is_local()) {
    /**
     * グローバルに呼び出せる汎用関数の一部サンプルです。
     * ざっくりこんな関数が呼べるんだなと把握いただければ削除してください。
     */

    // echo home();
    // echo theme_uri();
    // echo theme_dir();
    // echo img_dir();
    // echo url('company');

    // the_component('searchform', ['hoge' => $fuga]);

    // foreach (config('app.runtime.local') as $t) {
    //     echo $t . '<br>';
    // }

    // $query = wpquery()
    //     ->setPostType(['news'])
    //     ->setPerPage(10)
    //     ->setOrderByDate()
    //     // ->debug();
    //     ->build();
    // d($query);

    // slog()->info('test');

    // $post = db()->stmt('SELECT * FROM wp_posts WHERE ID = %d', [1])->get();
    // d($post);

    // $client = http_client();
    // $res    = $client->get(home() . '/wp-json/wp/v2/posts', []);
    // d($res[]);

    // http_sess()->set('allmanage', 'hoge');
    // http_sess()->debug();

    // pdf_writer(['key' => 'hoge'], 'sample.php', 'sample', false);
}
?>

<main class="p-home">
    <div class="p-home_kv">
        <div class="p-home_kv__img">
            <picture class="img c-ofi">
                <source srcset="<?= img_dir(); ?>/home/img_kv.jpg" media="(min-width: 768px)" />
                <img src="<?= img_dir(); ?>/home/img_kv.jpg" alt="" class="c-ofi__img">
            </picture>
        </div>
        <div class="p-home_kv__catch">
            <div class="jp"></div>
            <div class="en"></div>
        </div>
    </div>
    <div class="p-home__body">
        <section class="">
            <div class="c-inner">

            </div>
        </section>
    </div>
</main>
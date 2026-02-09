<?php
$name = config('seo.name');
?>

<main class="p-404">
  <div class="p-kv_under">
    <div class="c-inner">
      <div class="p-kv_under__ttl">
        <div class="en">NOT FOUND</div>
        <h1 class="jp"><span>404</span></h1>
      </div>
    </div>
  </div>
  <div class="l-content -under">
    <section class="">
      <div class="c-inner">
        <div class="p-404__head">
          <h2 class="p-404__ttl">ページが見つかりませんでした</h2>
        </div>
        <div class="p-404__body">
          <p>
            この度は<?= $name ?>のホームページにお越しいただき誠にありがとうございます。<br>申し訳ございませんがお探しのページもしくはファイルは見つかりませんでした。<br>ページが移動した可能性もございますのでお手数ですが、下記「サイトマップ」より該当のページをお探しください。
          </p>
        </div>
        <div class="u-mgt_xxl u-center">
          <a href="<?= url('top') ?>" class="c-btn">
            <span class="c-btn__txt">TOPへ戻る</span>
            <div class="c-btn__icon"></div>
          </a>
        </div>
      </div>
    </section>
  </div>
</main>
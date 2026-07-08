<?php

namespace App\Hooks;

use App\Interfaces\BootableWpHookInterface;
use App\Services\Http\Runtime;
use App\Services\Config;
use App\Presenters\Metadata;

/**---------------------------------------------
 * SEO 関連フッククラス
 * ---------------------------------------------
 * - noindex / nofollow 制御
 * - meta / OGP / JSON-LD 等の head 出力を担当
 */
class AddHeadMetadata implements BootableWpHookInterface
{
  public function boot(): void
  {
    add_filter('option_blog_public', $this->defaultLocalNoindex(...));
    add_filter('wp_robots', $this->addNoindex(...));
    add_action('wp_head', $this->addMetadata(...));
    add_action('wp_body_open', $this->addGtmBody(...));
  }

  /**
   * 本番以外は管理画面のnoindex設定をnoindexに固定
   */
  public function defaultLocalNoindex($value)
  {
    if (Runtime::isLocal()) {
      return 0;
    }

    return $value;
  }

  /**
   * 本番以外はnoindex設定
   */
  public function addNoindex($robots): array
  {
    if (Runtime::isLocal()) {
      return \array_merge($robots, [
        'noindex'  => true,
        'nofollow' => true,
      ]);
    }

    return $robots;
  }

  /**
   * headを設定
   */
  public function addMetadata(): void
  {
    echo Metadata::getBase();

    // AIOSEOが有効ならバッティングするため出力しない
    if (!Config::get('seo.use_all_in_one_seo')) {
      echo Metadata::getFull();
      echo Metadata::getJsonld();
    }

    echo Metadata::getGtags();
    echo Metadata::getGtmHead();
  }

  /**
   * <body>直後にGTMのnoscriptタグを出力する
   */
  public function addGtmBody(): void
  {
    echo Metadata::getGtmBody();
  }
}

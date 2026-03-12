<?php

namespace App\CMS\Hooks;

use App\Support\Runtime;
use App\CMS\Presenter\Metadata;
use App\Support\Config;

/**---------------------------------------------
 * SEO 関連フッククラス
 * ---------------------------------------------
 * - noindex / nofollow 制御
 * - meta / OGP / JSON-LD 等の head 出力を担当
 */
class Seo extends Hook
{
    /**
     * フック登録
     */
    #[\Override]
    public function boot(): void
    {
        add_filter('wp_robots', $this->addNoindex(...));
        add_action('wp_head', $this->addMetadata(...));
    }

    /**
     * 本番以外はnoindex設定
     */
    public function addNoindex($robots): array
    {
        // 本番または既に明示的にnoindexならスルー
        if (!Runtime::isLocal()) {
            return $robots;
        }
        if (!empty($robots['noindex'])) {
            return $robots;
        }

        $robots['noindex']  = true;
        $robots['nofollow'] = true;

        return $robots;
    }

    /**
     * headを設定
     */
    public function addMetadata(): void
    {
        echo Metadata::getBase();
        if (!Config::get('seo.use_all_in_one_seo')) {
            echo Metadata::getFull();
            echo Metadata::getJsonld();
        }
        echo Metadata::getGtags();
    }
}
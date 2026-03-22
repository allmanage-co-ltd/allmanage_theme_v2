<?php

namespace App\Hooks;

use App\Helpers\Path;
use App\Interfaces\BootableWpHookInterface;
use App\Services\Config;

/**---------------------------------------------
 * 管理画面オプションページ登録クラス
 * ---------------------------------------------
 * - 管理画面側で使用する Admin クラス
 * - Csv 入出力用の管理ページを追加する
 * - 表示可否は Config 設定により制御する
 * - add_menu_page を散らさない
 * - functions.php に管理画面ロジックを書かない
 * - 管理画面の表示有無を設定ファイル主導で切り替える
 * - View とロジックを分離する
 */
class RegisterOptionPage implements BootableWpHookInterface
{
    /**
     * 初期化処理
     */
    public function boot(): void
    {
        add_action('admin_menu', $this->register(...));
    }

    /**
     * 管理画面オプション登録
     */
    public function register(): void
    {
        foreach (Config::get('cms.option_pages') ?? [] as $key => $option) {
            if (empty($option['show'])) {
                continue;
            }

            add_menu_page(
                $option['page_title'],
                $option['menu_title'],
                $option['capability'] ?? 'manage_options',
                $option['slug'] ?? $key,
                fn() => $this->render($option),
            );
        }
    }

    /**
     * 管理画面View表示
     */
    private function render(array $option): void
    {
        $view = $option['view'] ?? null;

        if (!$view) {
            return;
        }

        $baseDir = Config::get('cms.option_view_dir') ?? Path::views('app/admin');
        $path    = Path::join(\rtrim($baseDir, '/'), $view);

        include $path;
    }
}

<?php

namespace App\Packages;

/**---------------------------------------------
 * ライブラリ起動インターフェース
 * ---------------------------------------------
 * このインターフェースを実装すると Library.php が自動的に boot() を呼び出す。
 * WordPress フックの登録など、アプリ起動時に一度だけ実行したい処理をここに書く。
 *
 * ---------------------------------------------
 * ■ 実装クラスの置き場所
 * ---------------------------------------------
 * 原則として app/Library/Hooks/ 以下に置く。
 * ただし強制ではなく、app/Library/ 以下であればどこでも動作する。
 *
 * ---------------------------------------------
 * ■ 自動発見の仕組み（classmap）
 * ---------------------------------------------
 * Library.php は Composer の classmap を走査して BootableInterface の実装を見つける。
 * classmap はファイルを追加・削除したときに手動で再生成が必要:
 *
 *   composer dump-autoload
 *
 * WordPress 環境では通常 functions.php と同じ階層で実行する。
 * Composer が入っていない場合は開発担当者に依頼すること。
 *
 * ---------------------------------------------
 * ■ 使い方
 * ---------------------------------------------
 *   class MyHook implements BootableInterface
 *   {
 *       public function boot(): void
 *       {
 *           add_action('init', $this->register(...));
 *       }
 *
 *       private function register(): void { ... }
 *   }
 */
interface BootableInterface
{
    public function boot(): void;
}

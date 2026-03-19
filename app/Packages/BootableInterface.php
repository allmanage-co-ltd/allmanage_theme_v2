<?php

namespace App\Packages;

/**---------------------------------------------
 * パッケージ起動インターフェース
 * ---------------------------------------------
 * config/packages.php の hook_providers に登録されたクラスに対して
 * App\CMS\Hooks\Package が boot() を呼び出すためのインターフェース。
 *
 * WordPress フックの登録など、
 * アプリ起動時に一度だけ実行したい処理をここに書く。
 *
 * ---------------------------------------------
 * ■ 実装クラスの置き場所
 * ---------------------------------------------
 * app/Packages/ 以下に置く想定。
 * ただし実際に起動されるかどうかは配置場所ではなく
 * config/packages.php の hook_providers への登録で決まる。
 *
 * ---------------------------------------------
 * ■ 使い方
 * ---------------------------------------------
 * 1. BootableInterface を実装したクラスを作成する
 * 2. config/packages.php の hook_providers にクラスを追加する
 * 3. Package Hook 経由で boot() が呼び出される
 */
interface BootableInterface
{
    public function boot(): void;
}

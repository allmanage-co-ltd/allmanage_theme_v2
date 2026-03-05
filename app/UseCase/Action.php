<?php

namespace App\UseCase;

use App\Support\Config;

/**---------------------------------------------
 * Action基底クラス
 * ---------------------------------------------
 * ユースケース単位の処理を表現する。
 * 詳細な設計思想は UseCase/README.md を参照。
 */
abstract class Action
{
    // 必要なら共通ユーティリティだけ置く
    protected readonly array|string $config;

    public function __construct()
    {
        $this->config = Config::get('app');
    }
}

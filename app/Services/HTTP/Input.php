<?php

namespace App\Services\HTTP;

/**---------------------------------------------
 * リクエスト管理サービス
 * ---------------------------------------------
 * - $_GET / $_POST を直接触らせないためのラッパー
 * - 入力取得・ホワイトリスト抽出を一元管理する
 * - View / Controller / Service から共通APIで扱えるようにする
 */
class Input
{
    /**
     * POST優先で値を取得
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $_POST[$key]
            ?? $_GET[$key]
            ?? $default;
    }

    /**
     * 全リクエスト値取得
     * すべての入力値を返す（POST優先）
     */
    public function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    /**
     * 指定キーだけ取得
     */
    public function only(array $keys): array
    {
        $data = [];

        foreach ($keys as $key) {
            $data[$key] = $this->get($key);
        }

        return $data;
    }

    /**
     * 値が存在するか
     */
    public function has(string $key): bool
    {
        return isset($_POST[$key]) || isset($_GET[$key]);
    }
}

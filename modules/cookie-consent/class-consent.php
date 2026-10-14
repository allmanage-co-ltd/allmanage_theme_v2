<?php

/**
 * Consent — Cookie同意状態の管理クラス
 *
 * $_COOKIE['cookie_consent'] を JSON で読み書きし、
 * カテゴリ別の同意状態を提供する。
 */

namespace CookieConsent;

if (!defined('ABSPATH')) {
    exit;
}

class Consent
{
    /** Cookie名 */
    const COOKIE_NAME = 'cookie_consent';

    /** Cookie有効期限（秒）: 1年 */
    const COOKIE_EXPIRE = 60 * 60 * 24 * 365;

    /** 利用可能なカテゴリ */
    const CATEGORIES = ['necessary', 'functional', 'performance', 'marketing'];

    /** パース済み同意データ（null = 未取得） */
    private ?array $data = null;

    /**
     * Cookie が存在するか（初回訪問かどうかの判定に使う）
     */
    public function exists(): bool
    {
        return isset($_COOKIE[self::COOKIE_NAME]);
    }

    /**
     * カテゴリ別の同意状態を返す
     *
     * @param string $category necessary | functional | performance | marketing
     */
    public function has(string $category): bool
    {
        // necessary は常に true
        if ($category === 'necessary') {
            return true;
        }

        $data = $this->getData();

        // Cookie 未設定（初回訪問）はすべて false
        if ($data === null) {
            return false;
        }

        return !empty($data[$category]);
    }

    /**
     * すべてのカテゴリの同意状態を連想配列で返す
     *
     * @return array<string, bool>
     */
    public function getAll(): array
    {
        $data = $this->getData();

        return [
            'necessary'   => true,
            'functional'  => $data !== null && !empty($data['functional']),
            'performance' => $data !== null && !empty($data['performance']),
            'marketing'   => $data !== null && !empty($data['marketing']),
        ];
    }

    /**
     * Cookie から同意データをパースして返す
     *
     * @return array|null  パース成功時は連想配列、Cookie未設定時は null
     */
    private function getData(): ?array
    {
        if ($this->data !== null) {
            return $this->data;
        }

        if (!isset($_COOKIE[self::COOKIE_NAME])) {
            return null;
        }

        $raw = stripslashes($_COOKIE[self::COOKIE_NAME]);
        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            return null;
        }

        $this->data = $decoded;
        return $this->data;
    }
}

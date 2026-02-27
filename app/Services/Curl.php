<?php

namespace App\Services;

use RuntimeException;

/**---------------------------------------------
 * Curl HTTPラッパークラス
 * ---------------------------------------------
 * ■返却値フォーマット
 *   [ 'status' => int, 'body' => string ]
 *
 * ■基本的なGETリクエスト
 *   $res = Curl::request('https://api.example.com/users');
 *   if ($res->ok()) {
 *        d($res->body());
 *   }
 *
 * ■JSON POSTリクエスト
 *   $res = Curl::request('POST', 'https://api.example.com/users', [
 *       'json' => [
 *           'name' => 'hoge',
 *           'age'  => 30,
 *       ],
 *   ]);
 *
 * ■エラーハンドリング例
 *   try {
 *       $res = Curl::request('https://api.example.com/users');
 *       if (! $res->ok()) {
 *           throw new RuntimeException('APIエラー');
 *       }
 *   } catch (\RuntimeException $e) {
 *       Logger::new()->error($e->getMessage());
 *   }
 */
class Curl
{
    /**
     * レスポンス保持
     */
    private function __construct(
        private readonly int $status,
        private readonly string $body,
        private readonly array $headers = []
    ) {
    }

    /**
     * GET|POST
     */
    public static function request(string $method, string $url, array $options = []): self
    {
        return self::excute($method, $url, $options);
    }

    /**
     * 成功判定
     */
    public function ok(): bool
    {
        return $this->status >= 200 && $this->status < 300;
    }

    /**
     * ステータス取得
     */
    public function status(): int
    {
        return $this->status;
    }

    /**
     * 生ボディ取得
     */
    public function body(): string
    {
        return $this->body;
    }

    /**
     * 生ヘッダー取得
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * JSONデコード
     */
    public function decode(bool $assoc = true): array|object
    {
        $decoded = json_decode($this->body, $assoc);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('JSONデコード失敗');
        }

        return $decoded;
    }

    /**
     * 内部リクエスト処理
     */
    private static function excute(
        string $method,
        string $url,
        array $options = []
    ): self {

        $ch = curl_init();

        if (!$ch) {
            throw new RuntimeException('cURL初期化失敗');
        }

        $headers = [];

        if (!empty($options['headers'])) {
            foreach ($options['headers'] as $key => $value) {
                $headers[] = "{$key}: {$value}";
            }
        }

        $body = null;

        if (!empty($options['json'])) {
            $body      = json_encode($options['json']);
            $headers[] = 'Content-Type: application/json';
        }

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => $options['timeout'] ?? 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_POSTFIELDS     => $body,
        ]);

        $responseBody = curl_exec($ch);

        if ($responseBody === false) {
            $error = curl_error($ch);
            throw new RuntimeException("HTTPエラー: {$error}");
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $info   = curl_getinfo($ch);

        return new self(
            status: $status,
            body: $responseBody,
            headers: $info
        );
    }
}

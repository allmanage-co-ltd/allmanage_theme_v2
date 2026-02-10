<?php

namespace App\Services\Http;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;

/**---------------------------------------------
 * Guzzle HTTP Client
 * ---------------------------------------------
 *
 * $client = new \App\Services\Http\Client();
 * $res = $client->get('https://api.example.com/users', [
 *   'query' => ['page' => 1],
 *   'headers' => ['Accept' => 'application/json'],
 * ]);
 * $data = $client->decodeJson($res);
 */
class Client
{
    // Guzzleインスタンス
    private Guzzle $client;

    public function __construct(array $config = [])
    {
        $this->client = new Guzzle([
            'timeout' => $config['timeout'] ?? 10,
            'verify'  => $config['verify'] ?? false,
        ]);
    }

    /**
     * GETリクエスト
     */
    public function get(string $url, array $options = []): array
    {
        return $this->request('GET', $url, $options);
    }

    /**
     * POSTリクエスト
     */
    public function post(string $url, array $options = []): array
    {
        return $this->request('POST', $url, $options);
    }

    /**
     * JSONレスポンスを配列に変換する
     */
    public function decodeJson(array $response, bool $assoc = true): array|object
    {
        $decoded = json_decode($response['body'] ?? '', $assoc);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('JSONのデコードに失敗しました: ' . json_last_error_msg());
        }

        if (!is_array($decoded) && !is_object($decoded)) {
            throw new RuntimeException('JSONレスポンスの形式が不正です');
        }

        return $decoded;
    }

    /**
     * ステータスコードが2xxのときtrue
     */
    public function isOk(array $response): bool
    {
        $status = (int) ($response['status'] ?? 0);

        return $status >= 200 && $status < 300;
    }

    /**
     * リクエスト共通処理
     */
    private function request(string $method, string $url, array $options): array
    {
        try {
            $response = $this->client->request($method, $url, [
                'headers'     => $options['headers'] ?? [],
                'query'       => $options['query'] ?? [],
                'json'        => $options['json'] ?? null,
                'form_params' => $options['form'] ?? null,
            ]);
        } catch (GuzzleException $e) {
            throw new RuntimeException("HTTP通信に失敗しました: {$e->getMessage()}");
        }

        return [
            'status' => $response->getStatusCode(),
            'body'   => (string) $response->getBody(),
            'headers' => $response->getHeaders(),
        ];
    }
}

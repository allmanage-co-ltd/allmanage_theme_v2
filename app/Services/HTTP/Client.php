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
 * $response = $client->get('https://api.example.com/users', [
 *     'query' => ['page' => 1],
 *     'headers' => [
 *         'Accept' => 'application/json',
 *     ],
 *  ]);
 * $data = json_decode($response['body'], true);
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
        ];
    }
}

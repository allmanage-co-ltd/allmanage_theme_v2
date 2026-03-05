<?php

namespace App\Support;

use RuntimeException;

/**---------------------------------------------
 * Csvサポートクラス
 * ---------------------------------------------
 * - このクラスはあくまでI/O専用
 * - 投稿取得・データ変換はAction側で行う
 *
 * ■ファイルに保存する場合
 *   $rows = [
 *       ['ID', 'Title'],
 *       [1, 'Hello'],
 *       [2, 'World'],
 *   ];
 *   $csv = new Csv(path: '/tmp/posts.csv',  withBom: true);
 *   $csv->write($rows);
 *
 * ■管理画面からダウンロード出力する場合
 *   header('Content-Type: text/csv; charset=UTF-8');
 *   header('Content-Disposition: attachment; filename=posts.csv');
 *   $csv = new Csv(withBom: true);
 *   $csv->write($rows);
 *   exit;
 *
 * ■Excel（Windows）向けSJIS出力
 *   $csv = new Csv(encoding: 'SJIS-win');
 */
class Csv
{
    public function __construct(
        private readonly string $path = 'php://output',
        private readonly string $delimiter = ',',
        private readonly string $enclosure = '"',
        private readonly string $escape = '\\',
        private readonly bool $withBom = false,
        private readonly ?string $encoding = null
    ) {
        //
    }

    /**
     * 読み込み
     */
    public function read(): array
    {
        if (!file_exists($this->path)) {
            throw new RuntimeException("Csvファイルが存在しません: {$this->path}");
        }

        $fp = fopen($this->path, 'r');

        if (!$fp) {
            throw new RuntimeException("Csvを開けません: {$this->path}");
        }

        $rows = [];

        while (($data = fgetcsv($fp, 0, $this->delimiter, $this->enclosure, $this->escape)) !== false) {
            $rows[] = $data;
        }

        fclose($fp);

        return $rows;
    }

    /**
     * 書き込み
     */
    public function write(iterable $rows): void
    {
        $fp = fopen($this->path, 'w');

        if (!$fp) {
            throw new RuntimeException("Csvを書き込めません: {$this->path}");
        }

        // BOM（Excel対策）
        if ($this->withBom && $this->encoding === null) {
            fwrite($fp, "\xEF\xBB\xBF");
        }

        foreach ($rows as $row) {
            if (!is_array($row)) {
                throw new RuntimeException("Csvの行は配列である必要があります");
            }

            $row = $this->convertEncoding($row);

            if (fputcsv($fp, $row, $this->delimiter, $this->enclosure, $this->escape) === false) {
                throw new RuntimeException("Csv書き込みに失敗しました");
            }
        }

        fclose($fp);
    }

    /**
     * 文字コード変換
     */
    private function convertEncoding(array $row): array
    {
        if ($this->encoding === null) {
            return $row;
        }

        return array_map(function ($value) {
            if (!is_string($value)) {
                return $value;
            }
            return mb_convert_encoding($value, $this->encoding, 'UTF-8');
        }, $row);
    }
}

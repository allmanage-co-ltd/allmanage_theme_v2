<?php

namespace App\Services\Csv;

/**---------------------------------------------
 * CSV書き込みクラス
 * ---------------------------------------------
 * - CSVファイル/出力先へのI/O専用
 * - データ変換は最小限に留め、業務ロジックは呼び出し側で扱う
 */
class CsvWriter
{
  public function __construct(
    private readonly string $delimiter = ',',
    private readonly string $enclosure = '"',
    private readonly string $escape = '\\',
    private readonly bool $withBom = false,
    private readonly ?string $encoding = null
  ) {
    //
  }

  /**
   * CSVを書き込む
   */
  public function execute(iterable $rows, $path = 'php://output'): void
  {
    $fp = \fopen($path, 'w');

    if (!$fp) {
      throw new \RuntimeException("Csvを書き込めません: {$path}");
    }

    try {
      if ($this->withBom && $this->encoding === null) {
        \fwrite($fp, "\xEF\xBB\xBF");
      }

      foreach ($rows as $row) {
        if (!\is_array($row)) {
          throw new \RuntimeException('Csvの行は配列である必要があります');
        }

        $row = $this->convertEncoding($row);

        if (\fputcsv($fp, $row, $this->delimiter, $this->enclosure, $this->escape) === false) {
          throw new \RuntimeException('Csv書き込みに失敗しました');
        }

        if ($path === 'php://output') {
          \fflush($fp);
        }
      }
    } finally {
      \fclose($fp);
    }
  }

  /**
   * 行データの文字コードを変換する
   */
  private function convertEncoding(array $row): array
  {
    return \array_map(function ($value) {
      if (\is_array($value)) {
        $value = \implode(',', $value);
      }

      if (\is_bool($value)) {
        $value = $value ? '1' : '0';
      }

      if ($value === null) {
        $value = '';
      }

      if ($this->encoding !== null && \is_string($value)) {
        return \mb_convert_encoding($value, $this->encoding, 'UTF-8');
      }

      return (string) $value;
    }, $row);
  }
}

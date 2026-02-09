<?php

namespace App\Services\CSV;

use App\Services\Config;

/**---------------------------------------------
 * CSVクラス
 * ---------------------------------------------
 *
 * 作成中
 *
 */
class CSV
{
  readonly public array $csv_config;
  readonly public array $data_model;

  public function __construct()
  {
    $this->csv_config = Config::get('csv');
    $this->data_model = Config::get('csv.data');
  }

  /**
   *
   */
  public static function reader(array|string $f): array
  {
    return [];
  }

  /**
   *
   */
  public static function weiter(
    array|null $header,
    array $rows,
    array $out = ['php://output', 'w'],
  ) {

  }

}
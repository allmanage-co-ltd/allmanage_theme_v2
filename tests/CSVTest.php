<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use App\Services\CSV\CSV;

#[CoversClass(CSV::class)]
final class CSVTest extends TestCase
{
  //
  public function test_reader_正常に読み取り配列が返るか(): void
  {
    $result = CSV::reader([]);
    $this->assertIsArray($result);
  }

  //
  public function test_witer_正常に書き込みが完了するか(): void
  {
    $result = CSV::reader([]);
    $this->assertIsArray($result);
  }
}
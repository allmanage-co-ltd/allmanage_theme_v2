<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
// use PHPUnit\Framework\Attributes\CoversClass;


#[CoversNothing]                       // クラスではないものをテストする場合のアトリビュート
// #[CoversClass(Example::class)]      // クラスをテストする場合のアトリビュート
final class ExampleTest extends TestCase
{
  public function test_example_テストサンプルです(): void
  {
    /** ===============================
     * 型・存在チェック系
     * =============================== */
    $this->assertTrue(true);                      // trueであること
    $this->assertFalse(false);                    // falseであること
    $this->assertNull(null);                      // nullであること
    $this->assertNotNull('a');                    // nullではないこと
    $this->assertIsArray([]);                     // 配列である
    $this->assertIsString('a');                   // 文字列である
    $this->assertIsInt(1);                        // intである
    $this->assertIsBool(true);                    // boolである
    $this->assertIsObject(new stdClass());        // オブジェクトである
    $this->assertIsCallable(fn(): null => null);  // callableである

    /** ===============================
     * 値の一致・比較
     * =============================== */
    $this->assertSame(1, 1);                  // 型も値も完全一致（===）
    $this->assertNotSame(1, '1');             // 型 or 値が違う
    $this->assertEquals(1, '1');              // 値が等しい（==）
    $this->assertNotEquals(1, 2);             // 値が等しくない
    $this->assertEmpty([]);                   // emptyである
    $this->assertNotEmpty([1]);               // emptyではない

    /** ===============================
     * 配列系
     * =============================== */
    $this->assertCount(2, [1, 2]);             // 要素数チェック
    $this->assertContains(1, [1, 2, 3]);       // 値が含まれる
    $this->assertNotContains(4, [1, 2, 3]);    // 値が含まれない
    $this->assertArrayHasKey('a', ['a' => 1]);     // keyが存在
    $this->assertArrayNotHasKey('b', ['a' => 1]);  // keyが存在しない

    /** ===============================
     * 文字列系
     * =============================== */
    $this->assertStringContainsString('foo', 'foobar');     // 部分一致
    $this->assertStringNotContainsString('baz', 'foobar');  // 含まれない
    $this->assertMatchesRegularExpression('/foo/', 'foobar'); // 正規表現一致

    /** ===============================
     * クラス・インスタンス
     * =============================== */
    $this->assertInstanceOf(stdClass::class, new stdClass()); // クラス一致
    $this->assertObjectHasProperty('a', (object) ['a' => 1]); // プロパティ存在

    /** ===============================
     * 例外系（超重要）
     * =============================== */
    $this->expectException(RuntimeException::class); // 例外が投げられること
    throw new RuntimeException('error');

    /** ===============================
     * ファイル・パス系
     * =============================== */
    $this->assertFileExists(__FILE__);      // ファイルが存在
    $this->assertDirectoryExists(__DIR__);  // ディレクトリが存在
  }
}
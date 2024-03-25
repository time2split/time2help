<?php
declare(strict_types=1);
namespace Time2Split\Help\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Time2Split\Help\Optional;

/**
 * @author Olivier Rodriguez (zuri)
 */
final class OptionalTest extends TestCase
{
    public function testOptionalOf()
    {
        $val = 0;
        $opt = Optional::of($val);
        $this->assertTrue($opt->isPresent());
        $this->assertSame($val, $opt->get());
        $this->assertSame($val, $opt->orElse(null));
        $this->assertSame($val, $opt->orElseGet(fn () => null));
        $this->assertNull(Optional::of(null)->get());
    }

    public function testOptionalEmpty()
    {
        $val = 0;
        $opt = Optional::empty();
        $this->assertFalse($opt->isPresent());
        $this->assertNull($opt->orElse(null));
        $this->assertNull($opt->orElseGet(fn () => null));
        $this->assertSame($opt, Optional::empty());
    }

    public function testOptionalOfNullable()
    {
        $this->assertTrue(Optional::ofNullable(1)->isPresent());
        $this->assertFalse(Optional::ofNullable(null)->isPresent());
    }

    public static function _testOptionalGetException(): array
    {
        return [
            [fn () => Optional::empty ()],
            [fn () => Optional::ofNullable(null)],
        ];
    }

    #[DataProvider("_testOptionalGetException")]
    public function testOptionalGetException(\Closure $construct)
    {
        $this->expectException(\Error::class);
        $construct()->get();
    }
}
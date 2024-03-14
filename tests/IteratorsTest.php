<?php
declare(strict_types = 1);
namespace Time2Split\Help\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Time2Split\Help\Arrays;
use Time2Split\Help\Iterators;
use ArrayIterator;

final class IteratorsTest extends TestCase
{

    public static function isRewritingProvider(): array
    {
        $expect = [
            1,
            2,
            3
        ];
        return [
            'array' => [
                true,
                fn () => new ArrayIterator($expect),
                $expect
            ],
            'gen' => [
                false,
                fn () => (function () use ($expect) {
                    foreach ($expect as $k => $v)
                        yield $k => $v;
                })(),
                $expect
            ]
        ];
    }

    // ========================================================================
    #[DataProvider('isRewritingProvider')]
    public function testEnsureRewindableIterator(bool $isRewindable, \Closure $provideIterator, array $expect): void
    {
        $iterator = $provideIterator();

        $it = Iterators::tryEnsureRewindableIterator($iterator);

        $this->assertTrue(Arrays::listEquals($expect, $it));
        $this->assertTrue(Arrays::listEquals($expect, $it));

        if (! $isRewindable && $iterator instanceof \Iterator) {
            $this->expectException(\Exception::class);
            $iterator->rewind();
        }
    }
}
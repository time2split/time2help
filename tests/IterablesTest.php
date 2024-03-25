<?php
declare(strict_types=1);
namespace Time2Split\Help\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Time2Split\Help\Arrays;
use Time2Split\Help\Iterables;
use Time2Split\Help\Tests\DataProvider\Provided;

final class IterablesTest extends TestCase
{
    private const testIteratorMethodsArray = [
        'a' => 1,
        'b' => 2,
        'c' => 3
    ];

    private static function _makeIteratorTestMethod(string $method, string $moreHeader, $expect, ...$args): Provided
    {
        $header = "$method$moreHeader";
        $closure = \Closure::fromCallable("Time2Split\Help\Iterables::$method");
        return new Provided($header, [
            fn ($a) => $closure($a, ...$args),
            $expect
        ]);
    }

    private static function makeIteratorTestMethod(string $method, $expect, ...$args): Provided
    {
        return self::_makeIteratorTestMethod($method, "", $expect, ...$args);
    }

    public static function _testIteratorMethods(): iterable
    {
        $mapk = \strtoupper(...);
        $mapv = fn (int $v) => $v * 10;
        $mapped = [
            'A' => 10,
            'B' => 20,
            'C' => 30,
        ];
        $provided = [
            new Provided("array", [fn ($a) => $a]),
            new Provided("ArrayIterator", [fn ($a) => new \ArrayIterator($a)]),
        ];
        $methods = [
            new Provided('same', [
                fn ($a) => $a,
                self::testIteratorMethodsArray
            ]),
            self::makeIteratorTestMethod('keys', \array_keys(self::testIteratorMethodsArray)),
            self::makeIteratorTestMethod('values', \array_values(self::testIteratorMethodsArray)),
            self::makeIteratorTestMethod('flip', \array_flip(self::testIteratorMethodsArray)),
            self::makeIteratorTestMethod('reverse', \array_reverse(self::testIteratorMethodsArray)),
            self::makeIteratorTestMethod('reverseKeys', \array_reverse(\array_keys(self::testIteratorMethodsArray))),
            self::makeIteratorTestMethod('reverseValues', \array_reverse(\array_values(self::testIteratorMethodsArray))),
            self::makeIteratorTestMethod('reverseFlip', \array_reverse(\array_flip(self::testIteratorMethodsArray), true)),
            self::makeIteratorTestMethod('first', ['a' => 1]),
            self::makeIteratorTestMethod('last', ['c' => 3]),
            self::makeIteratorTestMethod('firstKey', 'a'),
            self::makeIteratorTestMethod('firstValue', 1),
            self::makeIteratorTestMethod('lastKey', 'c'),
            self::makeIteratorTestMethod('lastValue', 3),

            self::makeIteratorTestMethod('map', $mapped, $mapk, $mapv),
            self::makeIteratorTestMethod('mapKey', \array_combine(\array_keys($mapped), self::testIteratorMethodsArray), $mapk),
            self::makeIteratorTestMethod('mapValue', \array_combine(\array_keys(self::testIteratorMethodsArray), $mapped), $mapv),
        ];

        $limits = Arrays::mergeCartesianProduct(Arrays::cartesianProduct([0, 1, 2], [0, 1, 2, 3]));

        foreach ($limits as list($offset, $length)) {
            $expect = \array_slice(self::testIteratorMethodsArray, $offset, $length, true);
            $methods[] = self::_makeIteratorTestMethod('limit', ":$offset,$length", $expect, $offset, $length);
        }
        return Provided::merge($provided, $methods);
    }

    #[DataProvider("_testIteratorMethods")]
    public function testIteratorMethods(\Closure $construct, \Closure $test, $expect): void
    {
        $obj = $construct(self::testIteratorMethodsArray);
        $res = $test($obj);

        if (\is_iterable($res))
            $res = \iterator_to_array($res);

        $this->assertSame($expect, $res);
    }

    // ========================================================================

    public static function _testException(): iterable
    {
        $provide = [
            new Provided('0>offset', [fn () => Iterables::limit([], offset: -1)]),
            new Provided('0>length', [fn () => Iterables::limit([], length: -1)]),
        ];
        return Provided::merge($provide);
    }

    #[DataProvider("_testException")]
    public function testException(\Closure $test): void
    {
        $this->expectException(\DomainException::class);
        $a = $test();
        // Just to do something
        \iterator_to_array($a);
    }

    // ========================================================================

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
                fn () => new \ArrayIterator($expect),
                $expect
            ],
            'gen' => [
                false,
                fn () => (function () use ($expect) {
                    foreach ($expect as $k => $v) yield $k => $v;
                })(),
                $expect
            ]
        ];
    }

    #[DataProvider('isRewritingProvider')]
    public function testEnsureRewindableIterator(bool $isRewindable, \Closure $provideIterator, array $expect): void
    {
        $iterator = $provideIterator();

        $it = Iterables::ensureRewindableIterator($iterator);

        $this->assertTrue(Arrays::listEquals($expect, $it));
        $this->assertTrue(Arrays::listEquals($expect, $it));

        if (!$isRewindable && $iterator instanceof \Iterator) {
            $this->expectException(\Exception::class);
            $iterator->rewind();
        }
    }
}
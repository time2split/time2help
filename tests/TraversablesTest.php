<?php
declare(strict_types=1);
namespace Time2Split\Help\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Time2Split\Help\Tests\DataProvider\Provided;

final class TraversablesTest extends TestCase
{
    private const testIteratorMethodsArray = [
        'a' => 1,
        'b' => 2,
        'c' => 3
    ];

    private static function makeIteratorTestMethod(string $method, $expect): Provided
    {
        $closure = \Closure::fromCallable("Time2Split\Help\Traversables::$method");
        return new Provided($method, [
            fn ($a) => $closure($a),
            $expect
        ]);
    }

    public static function _testIteratorMethods(): iterable
    {
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
        ];
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
}
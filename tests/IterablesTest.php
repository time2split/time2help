<?php

declare(strict_types=1);

namespace Time2Split\Help\Tests;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Time2Split\Help\Arrays;
use Time2Split\Help\Iterable\ParallelFlag;
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

        $limits = Iterables::cartesianProductMerger([0, 1, 2], [0, 1, 2, 3]);

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

    public static function _testCountTraversable(array $array)
    {
        return new class($array) extends \ArrayObject
        {

            private bool $count = false;

            public function count(): int
            {
                $this->count = true;
                return 3;
            }

            public function calledCount(): bool
            {
                return $this->count;
            }
        };
    }
    public static function _testCount(): iterable
    {
        $array = self::testIteratorMethodsArray;
        $expect = \count($array);
        $provide = [
            new Provided('array', [
                fn () => Iterables::count($array),
                $expect
            ]),
            new Provided('Traversable', [
                function () use ($array) {
                    $traversable = IterablesTest::_testCountTraversable($array);
                    $cnt = Iterables::count($traversable, false);
                    Assert::assertFalse($traversable->calledCount());
                    return $cnt;
                },
                $expect
            ]),
            new Provided('Traversable&count', [
                function () use ($array) {
                    $traversable = IterablesTest::_testCountTraversable($array);
                    $cnt = Iterables::count($traversable, true);
                    Assert::assertTrue($traversable->calledCount());
                    return $cnt;
                },
                $expect
            ]),
        ];
        return Provided::merge($provide);
    }

    #[DataProvider('_testCount')]
    public function testCount(\Closure $count, int $expect): void
    {
        $this->assertSame($expect, $count());
    }

    // ========================================================================

    #[Test]
    public function append()
    {
        $entries = [
            ['a' => 'A'],
            ['a' => 'B'],
            ['b' => 'B'],
        ];
        $it = Iterables::append(...$entries);

        $res = [];
        foreach ($it as $k => $v)
            $res[] = [$k => $v];

        $this->assertSame($entries, $res);
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

        $this->assertTrue(Iterables::listEquals($expect, $it));
        $this->assertTrue(Iterables::listEquals($expect, $it));

        if (!$isRewindable && $iterator instanceof \Iterator) {
            $this->expectException(\Exception::class);
            $iterator->rewind();
        }
    }
    private static function makeSequenceTest(TestSequenceData $a, TestSequenceData $b, TestSequenceType $testType, bool $strictCmp, bool $expect = true): Provided
    {
        $e = $expect ? 'true' : 'false';
        $s = $strictCmp ? 'strict ' : '';
        $header = "$a $s$testType->name $b is $e";

        if (!$strictCmp)
            $test = match ($testType) {
                TestSequenceType::Equals => Iterables::sequenceEquals(...),
                TestSequenceType::Prefix => Iterables::sequencePrefixEquals(...),
                TestSequenceType::StrictPrefix => fn ($a, $b) => Iterables::sequencePrefixEquals($a, $b, strictPrefix: true),
                TestSequenceType::ListEquals => Iterables::listEquals(...),
                TestSequenceType::ListPrefix => Iterables::listPrefixEquals(...),
                TestSequenceType::ListStrictPrefix => fn ($a, $b) => Iterables::ListPrefixEquals($a, $b, strictPrefix: true),
            };
        else
            $test = match ($testType) {
                TestSequenceType::Equals => fn ($a, $b) => Iterables::sequenceEquals($a, $b, true, true),
                TestSequenceType::Prefix => fn ($a, $b) => Iterables::sequencePrefixEquals($a, $b, true, true),
                TestSequenceType::StrictPrefix => fn ($a, $b) => Iterables::sequencePrefixEquals($a, $b, true, true, true),
                TestSequenceType::ListEquals => fn ($a, $b) => Iterables::ListEquals($a, $b, true),
                TestSequenceType::ListPrefix => fn ($a, $b) => Iterables::ListPrefixEquals($a, $b, true),
                TestSequenceType::ListStrictPrefix => fn ($a, $b) => Iterables::ListPrefixEquals($a, $b, true, true),
            };

        return new Provided($header, [
            $expect,
            $test,
            $a->sequence,
            $b->sequence,
        ]);
    }

    // ========================================================================

    private static function makeListPrefixTest(TestSequenceData $a, TestSequenceData $b, bool $strictCmp, bool $strictPrefix = false, bool $expect = true): array
    {
        if ($strictPrefix)
            return [
                self::makeSequenceTest($a, $b, TestSequenceType::ListStrictPrefix, $strictCmp, $expect),
            ];
        else
            return [
                self::makeSequenceTest($a, $b, TestSequenceType::ListPrefix, $strictCmp, $expect),
            ];
    }
    private static function _makePrefixTest(TestSequenceData $a, TestSequenceData $b, bool $strictCmp, bool $strictPrefix = false, bool $expect = true): array
    {
        if ($strictPrefix)
            $ret = [
                self::makeSequenceTest($a, $b, TestSequenceType::StrictPrefix, $strictCmp, $expect),
            ];
        else
            $ret = [
                self::makeSequenceTest($a, $b, TestSequenceType::Prefix, $strictCmp, $expect),
            ];

        return [
            ...$ret,
            ...self::makeListPrefixTest($a, $b, $strictCmp, $strictPrefix, $expect),
        ];
    }

    private static function makePrefixTest(TestSequenceData $a, TestSequenceData $b, bool $strictCmp, bool $strictPrefix = false, bool $expect = true): array
    {
        $ret = self::_makePrefixTest($a, $b, $strictCmp, $strictPrefix, $expect);

        if ($expect) {

            if ($strictPrefix)
                $ret = \array_merge($ret, self::_makePrefixTest($a, $b, $strictCmp, false, true));
        } elseif (!$strictPrefix)
            $ret = \array_merge($ret, self::_makePrefixTest($a, $b, $strictCmp, true, false));

        return $ret;
    }

    // ========================================================================

    private static function _makeEqualTest(TestSequenceData $a, TestSequenceData $b, bool $strictCmp, bool $expect = true): array
    {
        $ret = [
            self::makeSequenceTest($a, $b, TestSequenceType::Equals, $strictCmp, $expect),
            self::makeSequenceTest($a, $b, TestSequenceType::ListEquals, $strictCmp, $expect),
        ];
        if ($expect) {
            $ret = \array_merge($ret, self::makePrefixTest($a, $b, $strictCmp, false, $expect));

            if ($strictCmp)
                $ret = \array_merge($ret, self::_makeEqualTest($a, $b, false, true));
        } else {

            if (!$strictCmp)
                $ret = \array_merge($ret, self::_makeEqualTest($a, $b, true, true));
        }
        return $ret;
    }

    private static function makeEqualTest(TestSequenceData $a, TestSequenceData $b, bool $strictCmp, bool $expect = true): array
    {
        if ($a === $b)
            return self::_makeEqualTest($a, $b, $strictCmp, $expect);
        else
            return [
                ...self::_makeEqualTest($a, $b, $strictCmp, $expect),
                ...self::_makeEqualTest($b, $a, $strictCmp, $expect),
            ];
    }

    // ========================================================================

    public static function _testSequence(): iterable
    {
        $a = new TestSequenceData('a', ['a' => 1]);
        $a2 = new TestSequenceData('a2', ['a' => true]);
        $alist = new TestSequenceData('al', [1]);
        $b = new TestSequenceData('ab', [...$a->sequence, 'b' => 2]);
        $b2 = new TestSequenceData('ab2', [...$a2->sequence, 'b' => 2]);
        $positive = [
            ...self::makeEqualTest($a, $a, true),
            ...self::makeEqualTest($b, $b, true),
            ...self::makeEqualTest($a, $a2, false),
            ...self::makeEqualTest($b, $b2, false),

            ...self::makePrefixTest($a, $b, true),
            ...self::makePrefixTest($a, $b2, false),
            ...self::makePrefixTest($a2, $b, false),
            ...self::makePrefixTest($a2, $b2, true),

            self::makeSequenceTest($alist, $a, TestSequenceType::ListEquals, true),
            ...self::makeListPrefixTest($alist, $a, true),
            ...self::makeListPrefixTest($alist, $a2, false),
        ];
        $negative = [
            ...self::makeEqualTest($a, $b, true, false),
            ...self::makeEqualTest($a, $a2, true, false),
            ...self::makeEqualTest($b, $b2, true, false),

            ...self::makePrefixTest($b, $a, true, false, false),
            ...self::makePrefixTest($b2, $a, false, false, false),
            ...self::makePrefixTest($b, $a2, false, false, false),
            ...self::makePrefixTest($b2, $a2, true, false, false),
        ];
        return Provided::merge([...$positive, ...$negative]);
    }

    #[DataProvider('_testSequence')]
    public function testSequence(bool $expected, callable $test, iterable $a, iterable $b): void
    {
        $this->assertEquals($expected, $test($a, $b));
    }

    // ========================================================================

    public static function limitProvider(): iterable
    {
        $a = range(0, 10);
        $limits = [
            [0, null],
            [1, null],
            [10, null],
            [0, 1],
            [0, 10],
            [0, 100],
            [1, 1],
            [2, 5],
            [0, 0],
        ];
        $tlimits = [];

        foreach ($limits as [$offset, $length])
            $tlimits[] = new Provided("$offset:$length", [$offset, $length, \array_slice($a, $offset, $length, true)]);

        $titerables = [
            new Provided('array',  [$a]),
            new Provided('Iterator', [new \ArrayIterator($a)]),
        ];
        return Provided::merge($titerables, $tlimits);
    }

    #[Test]
    #[DataProvider("limitProvider")]
    public function limit(iterable $iterable, int $offset, ?int $length, array $expect): void
    {
        $limit = Iterables::limit($iterable, $offset, $length);
        $this->assertSame($expect, \iterator_to_array($limit));
    }

    // ========================================================================

    public static function parallelProvider(): iterable
    {
        $a = [10 => 'a', 11  => 'b', 12 => 'c'];
        $b = ['A', 'B', 'C'];
        $c = ['A', 'B'];
        $acall = [
            10 => 'a',
            0 => 'A',
            11  => 'b',
            1 => 'B',
        ];
        $ab = $acall + [12 => 'c', 2 => 'C'];
        $acany = $acall + [12 => 'c', '' => null];
        $data = [
            ['ab:all', [ParallelFlag::NEED_ALL, [$a, $b], $ab]],
            ['ab:any', [ParallelFlag::NEED_ANY, [$a, $b], $ab]],
            ['ac:all', [ParallelFlag::NEED_ALL, [$a, $c], $acall]],
            ['ac:any', [ParallelFlag::NEED_ANY, [$a, $c], $acany]],
        ];
        $titerables = [];

        foreach ($data as [$head, $data])
            $titerables[] = new Provided("$head", $data);

        return Provided::merge($titerables);
    }

    #[Test]
    #[DataProvider("parallelProvider")]
    public function parallel(ParallelFlag $flags, array $iterables, array $expect): void
    {
        $parallel = Iterables::parallelWithFlags($iterables, $flags);
        $this->assertSame($expect, \iterator_to_array($parallel));
    }

    // ========================================================================


    private static function range($a, $b, $step = 1): \Closure
    {
        return function () use ($a, $b, $step): \Generator {
            for ($i = $a; $i <= $b; $i += $step)
                yield $i;
        };
    }

    private static function cartesianResult(\Closure ...$generators): \Generator
    {
        $count = \count($generators);

        if ($count === 0)
            return [];
        elseif ($count === 1) {
            foreach (\array_shift($generators)() as $k => $v)
                yield [
                    [
                        $k => $v
                    ]
                ];
        } else {
            foreach (\array_shift($generators)() as $k => $v) {
                foreach (self::cartesianResult(...$generators) as $subProduct)
                    yield \array_merge([
                        [
                            $k => $v
                        ]
                    ], $subProduct);
            }
        }
    }

    public static function cartesianProductProvider(): array
    {
        return [
            '0' => [
                0
            ],
            '1' => [
                1,
                self::range(1, 1)
            ],
            '2' => [
                2,
                self::range(1, 2)
            ],
            '2x2' => [
                4,
                self::range(1, 2),
                self::range(10, 11)
            ],
            '2x2x2' => [
                8,
                self::range(1, 2),
                self::range(10, 11),
                self::range(100, 101)
            ],
            '2x0x2' => [
                0,
                self::range(1, 2),
                self::range(1, 0),
                self::range(100, 101)
            ]
        ];
    }

    private function checkCartesianResult(int $count, \Iterator $expected, \Iterator $result): void
    {
        $expected = \iterator_to_array($expected);
        $result = \iterator_to_array($result);

        $ce = \count($expected);
        $this->assertSame($count, $ce, 'Expected count');
        $this->assertSame($expected, $result);
    }

    #[DataProvider('cartesianProductProvider')]
    public function testCartesianProduct(int $count, \Closure ...$generators): void
    {
        $expected = self::cartesianResult(...$generators);
        $result = Iterables::cartesianProduct(...\array_map(fn ($g) => \iterator_to_array($g()), $generators));

        $expected = Iterables::cartesianProductMerger($expected);
        $result = Iterables::cartesianProductMerger($result);
        $this->checkCartesianResult($count, $expected, $result);
    }
}

// ============================================================================

enum TestSequenceType
{
    case Equals;
    case Prefix;
    case StrictPrefix;
    case ListEquals;
    case ListPrefix;
    case ListStrictPrefix;
}

class TestSequenceData
{

    public function __construct(public string $name, public iterable $sequence)
    {
    }

    public function __toString(): string
    {
        return $this->name;
    }
}

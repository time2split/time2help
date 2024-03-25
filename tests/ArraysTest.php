<?php
declare(strict_types = 1);
namespace Time2Split\Help\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Time2Split\Help\Arrays;
use Time2Split\Help\Tests\DataProvider\Provided;

use function \iterator_to_array as toArray;

final class ArraysTest extends TestCase
{
    private const testIteratorMethodsArray = [
        'a' => 1,
        'b' => 2,
        'c' => 3
    ];

    private static function makeIteratorTestMethod(string $method, $expect): Provided
    {
        $closure = \Closure::fromCallable("Time2Split\Help\Arrays::$method");
        return new Provided($method, [
            fn ($a) => $closure($a),
            $expect
        ]);
    }
    public static function _testIteratorMethods(): iterable
    {
        $provided = [
            new Provided("array", [fn ($a) => $a]),
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

    // ========================================================================

    public static function diffProvider(): array
    {
        $res = [
            [
                // Strict
                false,
                // a
                $a = [
                    "a" => "green",
                    "red",
                    "blue",
                    "red"
                ],
                // b
                $b = [
                    "b" => "green",
                    "yellow",
                    "red"
                ],
                // ab
                [
                    1 => "blue",
                    "red"
                ],
                // ba
                [
                    0 => "yellow"
                ]
            ],
            [
                // Strict
                false,
                // a
                [
                    1
                ],
                // b
                [
                    1,
                    2
                ],
                // ab
                [],
                // ba
                [
                    1 => 2
                ]
            ],
            [
                // Strict
                false,
                // a
                [
                    1
                ],
                // b
                [
                    1,
                    1
                ],
                // ab
                [],
                // ba
                [
                    1 => 1
                ]
            ],
            [
                // Strict
                false,
                // a
                [
                    1,
                    1
                ],
                // b
                [
                    1,
                    1.0
                ],
                // ab
                [],
                // ba
                []
            ],
            [
                // Strict
                true,
                // a
                [
                    1,
                    1
                ],
                // b
                [
                    1,
                    1.0
                ],
                // ab
                [
                    1 => 1
                ],
                // ba
                [
                    1 => 1.0
                ]
            ]
        ];
        return $res;
    }

    #[DataProvider('diffProvider')]
    public function testDiff(bool $strict, array $a, array $b, array $resultab, $resultba): void
    {
        $diff = \iterator_to_array(Arrays::searchValueWithoutEqualRelation($a, $b, $strict));
        $this->assertSame($resultab, $diff);
        $diff = \iterator_to_array(Arrays::searchValueWithoutEqualRelation($b, $a, $strict));
        $this->assertSame($resultba, $diff);

        $equals = empty($resultab) && empty($resultba);
        $this->assertSame($equals, Arrays::contentEquals($a, $b, $strict));
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
            foreach (\array_shift($generators)() as $k => $v)
                foreach (self::cartesianResult(...$generators) as $subProduct)
                    yield \array_merge([
                        [
                            $k => $v
                        ]
                    ], $subProduct);
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
        $result = Arrays::cartesianProduct(...\array_map(fn ($g) => \iterator_to_array($g()), $generators));

        $expected = Arrays::mergeCartesianProduct($expected);
        $result = Arrays::mergeCartesianProduct($result);
        $this->checkCartesianResult($count, $expected, $result);
    }

    // ========================================================================

    private static function makeSequenceTest(ArraysTestSequenceData $a, ArraysTestSequenceData $b, ArraysTestSequenceType $testType, bool $strictCmp, bool $expect = true): Provided
    {
        $e = $expect ? 'true' : 'false';
        $s = $strictCmp ? 'strict ' : '';
        $header = "$a $s$testType->name $b is $e";

        if (!$strictCmp)
            $test = match ($testType) {
                ArraysTestSequenceType::Equals => Arrays::sequenceEquals(...),
                ArraysTestSequenceType::Prefix => Arrays::sequencePrefixEquals(...),
                ArraysTestSequenceType::StrictPrefix => fn($a, $b) => Arrays::sequencePrefixEquals($a, $b, strictPrefix: true),
                ArraysTestSequenceType::ListEquals => Arrays::listEquals(...),
                ArraysTestSequenceType::ListPrefix => Arrays::listPrefixEquals(...),
                ArraysTestSequenceType::ListStrictPrefix => fn($a, $b) => Arrays::ListPrefixEquals($a, $b, strictPrefix: true),
            };
        else
            $test = match ($testType) {
                ArraysTestSequenceType::Equals => fn($a, $b) => Arrays::sequenceEquals($a, $b, true, true),
                ArraysTestSequenceType::Prefix => fn($a, $b) => Arrays::sequencePrefixEquals($a, $b, true, true),
                ArraysTestSequenceType::StrictPrefix => fn($a, $b) => Arrays::sequencePrefixEquals($a, $b, true, true, true),
                ArraysTestSequenceType::ListEquals => fn($a, $b) => Arrays::ListEquals($a, $b, true),
                ArraysTestSequenceType::ListPrefix => fn($a, $b) => Arrays::ListPrefixEquals($a, $b, true),
                ArraysTestSequenceType::ListStrictPrefix => fn($a, $b) => Arrays::ListPrefixEquals($a, $b, true, true),
            };

        return new Provided($header, [
            $expect,
            $test,
            $a->sequence,
            $b->sequence,
        ]);
    }

    // ========================================================================

    private static function makeListPrefixTest(ArraysTestSequenceData $a, ArraysTestSequenceData $b, bool $strictCmp, bool $strictPrefix = false, bool $expect = true): array
    {
        if ($strictPrefix)
            return [
                self::makeSequenceTest($a, $b, ArraysTestSequenceType::ListStrictPrefix, $strictCmp, $expect),
            ];
        else
            return [
                self::makeSequenceTest($a, $b, ArraysTestSequenceType::ListPrefix, $strictCmp, $expect),
            ];
    }
    private static function _makePrefixTest(ArraysTestSequenceData $a, ArraysTestSequenceData $b, bool $strictCmp, bool $strictPrefix = false, bool $expect = true): array
    {
        if ($strictPrefix)
            $ret = [
                self::makeSequenceTest($a, $b, ArraysTestSequenceType::StrictPrefix, $strictCmp, $expect),
            ];
        else
            $ret = [
                self::makeSequenceTest($a, $b, ArraysTestSequenceType::Prefix, $strictCmp, $expect),
            ];

        return [
            ...$ret,
            ...self::makeListPrefixTest($a, $b, $strictCmp, $strictPrefix, $expect),
        ];
    }

    private static function makePrefixTest(ArraysTestSequenceData $a, ArraysTestSequenceData $b, bool $strictCmp, bool $strictPrefix = false, bool $expect = true): array
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

    private static function _makeEqualTest(ArraysTestSequenceData $a, ArraysTestSequenceData $b, bool $strictCmp, bool $expect = true): array
    {
        $ret = [
            self::makeSequenceTest($a, $b, ArraysTestSequenceType::Equals, $strictCmp, $expect),
            self::makeSequenceTest($a, $b, ArraysTestSequenceType::ListEquals, $strictCmp, $expect),
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

    private static function makeEqualTest(ArraysTestSequenceData $a, ArraysTestSequenceData $b, bool $strictCmp, bool $expect = true): array
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
        $a = new ArraysTestSequenceData('a', ['a' => 1]);
        $a2 = new ArraysTestSequenceData('a2', ['a' => true]);
        $alist = new ArraysTestSequenceData('al', [1]);
        $b = new ArraysTestSequenceData('ab', [...$a->sequence, 'b' => 2]);
        $b2 = new ArraysTestSequenceData('ab2', [...$a2->sequence, 'b' => 2]);
        $positive = [
            ...self::makeEqualTest($a, $a, true),
            ...self::makeEqualTest($b, $b, true),
            ...self::makeEqualTest($a, $a2, false),
            ...self::makeEqualTest($b, $b2, false),

            ...self::makePrefixTest($a, $b, true),
            ...self::makePrefixTest($a, $b2, false),
            ...self::makePrefixTest($a2, $b, false),
            ...self::makePrefixTest($a2, $b2, true),

            self::makeSequenceTest($alist, $a, ArraysTestSequenceType::ListEquals, true),
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
}

enum ArraysTestSequenceType
{
    case Equals;
    case Prefix;
    case StrictPrefix;
    case ListEquals;
    case ListPrefix;
    case ListStrictPrefix;
}

class ArraysTestSequenceData
{

    public function __construct(public string $name, public iterable $sequence)
    {
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
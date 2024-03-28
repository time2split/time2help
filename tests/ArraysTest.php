<?php

declare(strict_types=1);

namespace Time2Split\Help\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;
use Time2Split\Help\Arrays;
use Time2Split\Help\Iterables;
use Time2Split\Help\Tests\DataProvider\Provided;

use function \iterator_to_array as toArray;

final class ArraysTest extends TestCase
{
    private const array_abc = [
        'a' => 1,
        'b' => 2,
        'c' => 3
    ];

    private const list_abc = ['a', 'b', 'c'];

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
                self::array_abc
            ]),
            self::makeIteratorTestMethod('keys', \array_keys(self::array_abc)),
            self::makeIteratorTestMethod('values', \array_values(self::array_abc)),
            self::makeIteratorTestMethod('flip', \array_flip(self::array_abc)),
            self::makeIteratorTestMethod('reverse', \array_reverse(self::array_abc)),
            self::makeIteratorTestMethod('reverseKeys', \array_reverse(\array_keys(self::array_abc))),
            self::makeIteratorTestMethod('reverseValues', \array_reverse(\array_values(self::array_abc))),
            self::makeIteratorTestMethod('reverseFlip', \array_reverse(\array_flip(self::array_abc), true)),
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
        $obj = $construct(self::array_abc);
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
        $diff = \iterator_to_array(Arrays::diffEntries($a, $b, $strict));
        $this->assertSame($resultab, $diff);
        $diff = \iterator_to_array(Arrays::diffEntries($b, $a, $strict));
        $this->assertSame($resultba, $diff);

        $equals = empty($resultab) && empty($resultba);
        $this->assertSame($equals, Arrays::sameEntries($a, $b, $strict));
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
        $result = Arrays::cartesianProduct(...\array_map(fn ($g) => \iterator_to_array($g()), $generators));

        $expected = Arrays::cartesianProductMerger($expected);
        $result = Arrays::cartesianProductMerger($result);
        $this->checkCartesianResult($count, $expected, $result);
    }

    // ========================================================================

    private static function testSubSelect_expect(array $array, array $keys): array
    {
        $ret = [];
        foreach ($keys as $k)
            $ret[$k] = $array[$k];
        return $ret;
    }

    public function testSubSelect(): void
    {
        $nb = \count(self::array_abc);
        $abckeys = \array_keys(self::array_abc);

        for ($i = 0; $i < $nb; $i++) {
            $keys = \array_slice($abckeys, 0, $i);
            $expect = self::testSubSelect_expect(self::array_abc, $keys);
            $this->assertSame($expect, Arrays::select(self::array_abc, $keys));

            if ($i < 2) continue;
            $keys = \array_reverse($keys);
            $expect = self::testSubSelect_expect(self::array_abc, $keys);
            $this->assertSame($expect, Arrays::select(self::array_abc, $keys));
        }
        $expect = ['a' => 1, 'x' => false];
        $this->assertSame($expect, Arrays::select(self::array_abc, ['a', 'x'], false));
    }

    // ========================================================================


    public function testListValueAsKey(): void
    {
        $default = true;
        $expect = \array_combine(self::list_abc, \array_fill(0, 3, $default));
        $this->assertSame($expect, Arrays::replaceIntKeyByItsValue($expect, $default));

        $it = Iterables::flip([new stdClass()]);
    }

    // ========================================================================
    // UPDATE
    // ========================================================================
}

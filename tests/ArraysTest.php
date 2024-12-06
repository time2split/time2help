<?php

declare(strict_types=1);

namespace Time2Split\Help\Tests;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Time2Split\Help\Arrays;
use Time2Split\Help\Iterables;
use Time2Split\Help\Tests\DataProvider\Provided;

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
        $diff = \iterator_to_array(Iterables::valuesInjectionDiff($a, $b, $strict));
        $this->assertSame($resultab, $diff, 'ab');
        $diff = \iterator_to_array(Iterables::valuesInjectionDiff($b, $a, $strict));
        $this->assertSame($resultba, $diff, 'ba');

        $equals = empty($resultab) && empty($resultba);
        $this->assertSame($equals, Iterables::valuesEquals($a, $b, $strict));
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
            $this->assertSame($expect, Arrays::arraySelect(self::array_abc, $keys));

            if ($i < 2) continue;
            $keys = \array_reverse($keys);
            $expect = self::testSubSelect_expect(self::array_abc, $keys);
            $this->assertSame($expect, Arrays::arraySelect(self::array_abc, $keys));
        }
        $expect = ['a' => 1, 'x' => false];
        $this->assertSame($expect, Arrays::arraySelect(self::array_abc, ['a', 'x'], false));
    }

    // ========================================================================
    // UPDATE
    // ========================================================================

    public static function _testDeleteKey(): iterable
    {
        $provided = [
            new Provided('removeEntry', [function (array &$a, mixed $k): void {
                $v = $a[$k] ?? null;
                $e = Arrays::removeEntry($a, $k);
                Assert::assertSame($v, $e);
            }]),
            new Provided('filter:useKey', [function (array &$a, mixed $k): void {
                Arrays::removeWithFilter($a, fn ($kk) => $kk === $k, ARRAY_FILTER_USE_KEY);
            }]),
            new Provided('filter:useBoth', [function (array &$a, mixed $k): void {
                Arrays::removeWithFilter($a, fn ($v, $kk) => $kk === $k, ARRAY_FILTER_USE_BOTH);
            }]),
        ];
        return Provided::merge($provided);
    }

    #[DataProvider("_testDeleteKey")]
    public function testDeleteKey(\Closure $delete): void
    {
        $array = self::array_abc;
        $delete($array, 'a');
        $this->assertTrue(!isset($array['a']));
        $expect = $array;
        $delete($array, 'x');
        $this->assertSame($expect, $array);
    }

    // ========================================================================

    public static function _testDeleteValue(): iterable
    {
        $provided = [
            new Provided('dropValues', [function (array &$a, ...$values): void {
                Arrays::dropValues($a, false, ...$values);
            }]),
            new Provided('dropStrictValues', [function (array &$a, ...$values): void {
                Arrays::dropValues($a, true, ...$values);
            }]),
            new Provided('filter', [function (array &$a, ...$values): void {
                Arrays::removeWithFilter($a, fn ($v) => \in_array($v, $values));
            }]),
            new Provided('filterBoth', [function (array &$a, ...$values): void {
                Arrays::removeWithFilter($a, fn ($v, $k) => \in_array($v, $values), ARRAY_FILTER_USE_BOTH);
            }]),
        ];
        $values = [[], [1], [1, 2], [1, 3], [1, 2, 3]];
        $values = \array_map(fn ($v) => new Provided(\implode(',', $v), [$v]), $values);
        return Provided::merge($provided, $values);
    }

    // ========================================================================

    private static function makeUpdate($array, $update)
    {
        foreach ($update as $k => $v)
            $array[$k] = $v;
        return $array;
    }

    public static function _testUpdate(): iterable
    {
        $provided = [
            new Provided('array', [fn ($a) => $a]),
            // new Provided('ArrayObject', [fn ($a) => new \ArrayObject($a)]),
        ];
        return Provided::merge($provided);
    }

    #[DataProvider("_testUpdate")]
    public function testUpdate($makeArray): void
    {
        $array = $makeArray(self::array_abc);
        $update = ['a' => 11, 'd' => 4];
        $expect = self::makeUpdate($array, $update);

        Arrays::update($array, $update);
        $this->assertSame($expect, $array);

        $array = $makeArray(self::array_abc);
        $e = $expect;
        unset($e['d']);
        Arrays::updateIfPresent($array, $update);
        $this->assertSame($e, $array);

        $array = $makeArray(self::array_abc);
        $e = $expect;
        $e['a'] = 1;
        Arrays::updateIfAbsent($array, $update);
        $this->assertSame($e, $array);
    }

    public static function _testUpdateException()
    {
        $provided = [
            new Provided('update/unexists', [
                function () {
                    $a = [];
                    $u = ['a' => 1];
                    Arrays::updateWithClosures($a, $u);
                }, \Exception::class
            ]),
            new Provided('update/exists', [
                function () {
                    $a = ['a' => 0];
                    $u = ['a' => 1];
                    Arrays::updateWithClosures($a, $u);
                }, \Exception::class
            ]),
        ];
        return Provided::merge($provided);
    }

    #[DataProvider('_testUpdateException')]
    public function testUpdateException($test, $expect): void
    {
        $this->expectException($expect);
        $test();
    }
}

<?php
declare(strict_types = 1);
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Time2Split\Config\Configurations;
use Time2Split\Help\Arrays;

final class ArraysTest extends TestCase
{

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
}
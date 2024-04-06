<?php

declare(strict_types=1);

namespace Time2Split\Help\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Time2Split\Help\Tests\DataProvider\Provided;
use Time2Split\Help\TreeArrays;

final class TreeArraysTest extends TestCase
{
    public static function _testUpdate(): iterable
    {
        $provided = [
            new Provided('update', [
                function (&$array, $update) {
                    TreeArrays::update($array, $update);
                },
            ]),
        ];
        return Provided::merge($provided);
    }
    #[DataProvider('_testUpdate')]
    public function testUpdate(\Closure $doUpdate): void
    {
        $array = ['a' => ['aa' => 1]];
        $update = [
            'a' => [
                'ab' => 2,
                'ac' => 3,
            ],
            'b' => 2,
        ];
        $expect = $update;
        $expect['a'] = [
            'aa' => 1,
            'ab' => 2,
            'ac' => 3,
        ];
        $doUpdate($array, $update);
        $this->assertSame($expect, $array);
    }

    public function testSetBranch(): void
    {
        $expect = ['a' => ['aa' => 1, 'ab' => 2]];
        $array = [];

        TreeArrays::setBranch($array, ['a', 'aa'], 1);
        TreeArrays::setBranch($array, ['a', 'ab'], 2);
        $this->assertSame($expect, $array);

        $p = &TreeArrays::follow($array, ['a', 'ab']);
        $p = 99;
        $this->assertSame(99, $array['a']['ab']);

        TreeArrays::setBranch($array, ['a', 'aa', 'aaa'], 1);
        $p = &TreeArrays::follow($array, ['a', 'aa', 'aaa']);
        $this->assertSame(1, $array['a']['aa']['aaa']);
    }

    public function testWalk(): void
    {
        $array = ['a' => ['aa' => 1, 'ab' => 2]];

        $this->assertSame(2, TreeArrays::getMaxDepth($array));
        $this->assertSame(2, TreeArrays::countBranches($array));
        $this->assertSame(4, TreeArrays::countNodes($array));

        $paths = [];
        TreeArrays::walkBranches($array, function (array $path, $value) use (&$paths) {
            $paths[] = [...$path, $value];
        });
        $expect = [
            ['a', 'aa', 1],
            ['a', 'ab', 2],
        ];
        $this->assertSame($expect, $paths);

        $branches = TreeArrays::branches($array);
        $expect = [
            ['a', 'aa'],
            ['a', 'ab'],
        ];
        $this->assertSame($expect, $branches);

        $paths = [];
        TreeArrays::walkBranches($array, function (array $path, $value) use (&$paths) {
            $paths[] = [...$path, $value];
        }, fn () => false);
        $expect = [['a', ['aa' => 1, 'ab' => 2]]];
        $this->assertSame($expect, $paths);

        $array = [2, [3, [4], 5]];
        $add = 0;
        TreeArrays::walkNodes($array, function ($v) use (&$add) {
            if (\is_int($v))
                $add += $v;
        });
        $this->assertSame(14, $add);
    }

    public function testRemove(): void
    {
        $base = [
            'a' => ['aa' => 1, 'ab' => 2],
            'b' => 2
        ];
        $expect = ['a' => ['ab' => 2], 'b' => 2];

        $array = $base;
        $r = TreeArrays::removeBranch($array, ['a', 'aa']);
        $this->assertSame([['a'], ['aa'], 1], $r);
        $this->assertSame($expect, $array);

        $array = $base;
        $r = TreeArrays::removeLeaf($array, ['a', 'aa']);
        $this->assertSame(1, $r);
        $this->assertSame($expect, $array);

        // Multiple
        $expect = ['a' => ['ab' => 2]];

        $array = $base;
        $r = TreeArrays::removeArrayBranches($array, ['a', 'aa'], ['b']);
        $this->assertSame([
            [['a'], ['aa'], 1],
            [[], ['b'], 2],
        ], $r);
        $this->assertSame($expect, $array);

        $array = $base;
        $r = TreeArrays::removeArrayLeaves($array, ['a', 'aa'], ['b']);
        $this->assertSame([1, 2], $r);
        $this->assertSame($expect, $array);
    }
}

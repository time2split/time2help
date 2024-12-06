<?php

declare(strict_types=1);

namespace Time2Split\Help\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Time2Split\Help\ArrayTrees;
use Time2Split\Help\IterableTrees;
use Time2Split\Help\Tests\DataProvider\Provided;

final class TreesTest extends TestCase
{
    public static function _testUpdate(): iterable
    {
        $provided = [
            new Provided('update', [
                function (&$array, $update) {
                    ArrayTrees::update($array, $update);
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

        IterableTrees::setBranch($array, ['a', 'aa'], 1);
        IterableTrees::setBranch($array, ['a', 'ab'], 2);
        $this->assertSame($expect, $array);

        $p = &IterableTrees::follow($array, ['a', 'ab']);
        $p = 99;
        $this->assertSame(99, $array['a']['ab']);

        IterableTrees::setBranch($array, ['a', 'aa', 'aaa'], 1);
        $p = &IterableTrees::follow($array, ['a', 'aa', 'aaa']);
        $this->assertSame(1, $array['a']['aa']['aaa']);
    }

    public function testWalk(): void
    {
        $array = ['a' => ['aa' => 1, 'ab' => 2]];

        $this->assertSame(2, IterableTrees::getMaxDepth($array));
        $this->assertSame(2, IterableTrees::countLeaves($array));
        $this->assertSame(4, IterableTrees::countNodes($array));

        $paths = [];
        IterableTrees::walkBranches(
            $array,
            onLeaf: function ($node, array $path) use (&$paths) {
                $paths[] = [...$path, $node];
            }
        );
        $expect = [
            ['a', 'aa', 1],
            ['a', 'ab', 2],
        ];
        $this->assertSame($expect, $paths);

        $branches = IterableTrees::branches($array);
        $expect = [
            ['a', 'aa'],
            ['a', 'ab'],
        ];
        $this->assertSame($expect, $branches);

        $paths = [];
        IterableTrees::walkBranches(
            $array,
            isNode: fn () => false,
            onLeaf: function ($node, array $path) use (&$paths) {
                $paths[] = [...$path, $node];
            },
        );
        $expect = [['a', ['aa' => 1, 'ab' => 2]]];
        $this->assertSame($expect, $paths);

        $array = [2, [3, [4], 5]];
        $add = 0;
        IterableTrees::walkNodes(
            $array,
            onAnyNode: function ($v) use (&$add) {
                if (\is_int($v))
                    $add += $v;
            }
        );
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
        $r = IterableTrees::removeBranch($array, ['a', 'aa']);
        $this->assertSame([['a'], ['aa'], 1], $r);
        $this->assertSame($expect, $array);

        $array = $base;
        $r = IterableTrees::removeLastEdge($array, ['a', 'aa']);
        $this->assertSame(1, $r);
        $this->assertSame($expect, $array);

        $array = $base;
        $expect = ['b' => 2];
        $r = IterableTrees::removeLastEdge($array, ['a']);
        $this->assertSame(['aa' => 1, 'ab' => 2], $r);
        $this->assertSame($expect, $array);

        // Multiple
        $expect = ['a' => ['ab' => 2]];

        // $array = $base;
        // $r = IterableTrees::removeArrayBranches($array, ['a', 'aa'], ['b']);
        // $this->assertSame([
        //     [['a'], ['aa'], 1],
        //     [[], ['b'], 2],
        // ], $r);
        // $this->assertSame($expect, $array);

        // $array = $base;
        // $r = IterableTrees::removeArrayLeaves($array, ['a', 'aa'], ['b']);
        // $this->assertSame([1, 2], $r);
        // $this->assertSame($expect, $array);
    }
}

<?php

declare(strict_types=1);

namespace Time2Split\Help\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Time2Split\Help\Set;
use Time2Split\Help\Sets;
use Time2Split\Help\Exception\UnmodifiableSetException;

final class SetTest extends TestCase
{

    public function testArrayKeys(): void
    {
        $set = Sets::arrayKeys();

        $this->assertFalse(isset($set['a']));
        $this->assertSame(0, \count($set));

        $set['a'] = true;
        $this->assertTrue(isset($set['a']));
        $this->assertSame(1, \count($set));
        $this->assertSame([
            'a'
        ], \iterator_to_array($set));

        // Unset

        unset($set['a']);
        $this->assertFalse(isset($set['a']));
        $this->assertSame(0, \count($set));

        $set['a'] = true;
        $set['a'] = false;
        $this->assertFalse(isset($set['a']));
        $this->assertSame(0, \count($set));

        $set->setMore(0, 1, 2, 3);
        $this->assertSame(4, \count($set));
        $set->unsetMore(1, 2);
        $this->assertSame(2, \count($set));
        $this->assertSame([
            0,
            3
        ], \iterator_to_array($set));
    }

    // ========================================================================
    public static function _testUnmodifiable(): iterable
    {
        return [
            [
                (function ($set) {
                    $set[4] = true;
                })
            ],
            [
                (function ($set) {
                    $set[4] = false;
                })
            ],
            [
                (function ($set) {
                    unset($set[4]);
                })
            ]
        ];
    }

    #[DataProvider('_testUnmodifiable')]
    public function testUnmodifiable(\Closure $test)
    {
        $set = Sets::arrayKeys();
        $set->setMore(0, 1, 2, 3);
        $set = Sets::unmodifiable($set);
        $this->expectException(UnmodifiableSetException::class);
        $test($set);
    }

    // ========================================================================
    public function testNull()
    {
        $set = Sets::null();

        $this->assertSame(0, \count($set));
        $this->assertFalse($set['a']);
        $this->assertSame([], \iterator_to_array($set));

        $this->assertSame($set, Sets::null());
    }

    #[DataProvider('_testUnmodifiable')]
    public function testNullException(\Closure $test)
    {
        $set = Sets::null();
        $this->expectException(UnmodifiableSetException::class);
        $test($set);
    }

    // ========================================================================

    public static function enumProvider(): iterable
    {
        return [
            [Sets::ofEnum(AUnitEnum::class)],
            [Sets::ofEnum(AUnitEnum::a)],
        ];
    }

    #[Test]
    #[DataProvider('enumProvider')]
    public function enum(Set $set)
    {
        $this->assertFalse($set[AUnitEnum::a]);
        $set[AUnitEnum::a] = true;
        $this->assertTrue($set[AUnitEnum::a]);
        $this->assertSame([
            AUnitEnum::a
        ], \iterator_to_array($set));
        $set[AUnitEnum::a] = false;
        $this->assertFalse($set[AUnitEnum::a]);
    }

    // ========================================================================

    public static function _testBackedEnum(): iterable
    {
        return [
            [Sets::ofBackedEnum(AnEnum::class)],
            [Sets::ofBackedEnum(AnEnum::a)],
            [Sets::ofEnum(AnEnum::class)],
            [Sets::ofEnum(AnEnum::a)],
        ];
    }

    #[DataProvider('_testBackedEnum')]
    public function testBackedEnum(Set $set)
    {
        $this->assertFalse($set[AnEnum::a]);
        $set[AnEnum::a] = true;
        $this->assertTrue($set[AnEnum::a]);
        $this->assertSame([
            AnEnum::a
        ], \iterator_to_array($set));
        $set[AnEnum::a] = false;
        $this->assertFalse($set[AnEnum::a]);
    }

    public static function _testBackedEnumException(): iterable
    {
        return [
            [
                (function ($set) {
                    $set[AnotherEnum::a] = true;
                })
            ],
            [
                (function ($set) {
                    Sets::ofBackedEnum('badClass');
                })
            ]
        ];
    }

    #[DataProvider('_testBackedEnumException')]
    public function testBackedEnumException(\Closure $test)
    {
        $set = Sets::ofBackedEnum(AnEnum::class);
        $this->expectException(\InvalidArgumentException::class);
        $test($set);
    }

    // ========================================================================

    #[Test]
    public function equals()
    {
        $a = Sets::arrayKeys()->setMore(0, 1, 2);
        $b = $a;
        $this->assertTrue(Sets::equals($a, $b), 'Not the sames');

        // Must be order independant
        $b = Sets::arrayKeys()->setMore(2, 1, 0);
        $this->assertTrue(Sets::equals($a, $b), 'Order dependency');

        $b = Sets::arrayKeys()->setMore(0, 1, 3);
        $this->assertFalse(Sets::equals($a, $b), 'Are equals');
    }

    #[Test]
    public function includedIn()
    {
        $a = Sets::arrayKeys()->setMore(0, 1, 2);
        $b = $a;
        $this->assertTrue(Sets::includedIn($a, $b), 'Not the sames');

        // Must be order independant
        $b = Sets::arrayKeys()->setMore(2, 1, 0);
        $this->assertTrue(Sets::includedIn($a, $b), 'Order dependency');
        $this->assertTrue(Sets::includedIn($b, $a), 'Order dependency');

        $a = Sets::arrayKeys()->setMore(0, 2);
        $this->assertTrue(Sets::includedIn($a, $b), 'Is not included');
        $this->assertFalse(Sets::includedIn($b, $a), 'Is included');

        $a = Sets::arrayKeys()->setMore(0, 3);
        $this->assertFalse(Sets::includedIn($a, $b), 'Is included');

        $a = Sets::arrayKeys()->setMore(0, 1, 3);
        $this->assertFalse(Sets::includedIn($a, $b), 'Is included');
    }
}

enum AUnitEnum
{

    case a;
}

enum AnEnum: int
{

    case a = 0;
}

enum AnotherEnum: int
{

    case a = 0;
}

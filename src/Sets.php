<?php
declare(strict_types = 1);
namespace Time2Split\Help;

use Time2Split\Help\Classes\NotInstanciable;
use Time2Split\Help\Exception\UnmodifiableSetException;
use Time2Split\Help\_private\Set\SetDecorator;

/**
 *
 * @author Olivier Rodriguez (zuri)
 */
final class Sets
{
    use NotInstanciable;

    /**
     * Get a {@link Set} storing items as keys of an array.
     *
     * This set is only convenient for data types that can fit as a array keys.
     *
     * @return Set A new set.
     */
    public static function arrayKeys(): Set
    {
        return new class() extends Set implements \IteratorAggregate {

            private array $storage = [];

            public function offsetGet(mixed $offset): bool
            {
                return $this->storage[$offset] ?? false;
            }

            public function count(): int
            {
                return \count($this->storage);
            }

            public function offsetSet(mixed $offset, mixed $value): void
            {
                if (! \is_bool($value))
                    throw new \InvalidArgumentException('Must be a boolean');

                if ($value)
                    $this->storage[$offset] = true;
                else
                    unset($this->storage[$offset]);
            }

            public function getIterator(): \Traversable
            {
                return new \ArrayIterator(\array_keys($this->storage));
            }
        };
    }

    /**
     * Get an arrayKeys set that can store arbitrary objects
     * as long as an object can be associated to a unique array key identifier.
     *
     * This class permits to handle more types of values and not just array key ones.
     * It makes a bijection between a valid array key and an object.
     *
     * @param \Closure $toKey
     *            Map an input item to a valid key.
     * @param \Closure $fromKey
     *            Retrieves the base object from the array key.
     * @return Set A new Set.
     */
    public static function toArrayKeys(\Closure $toKey, \Closure $fromKey): Set
    {
        return new class(self::arrayKeys(), $toKey, $fromKey) extends SetDecorator {

            public function __construct(Set $decorate, private readonly \Closure $toKey, private readonly \Closure $fromKey)
            {
                parent::__construct($decorate);
            }

            public function offsetSet(mixed $offset, mixed $value): void
            {
                $this->decorate->offsetSet(($this->toKey)($offset), $value);
            }

            public function offsetGet(mixed $offset): bool
            {
                return $this->decorate->offsetGet(($this->toKey)($offset));
            }

            public function getIterator(): \Traversable
            {
                foreach ($this->decorate as $k => $v)
                    yield $k => ($this->fromKey)($v);
            }
        };
    }

    /**
     * A set of a \BackedEnum instances.
     *
     * @param mixed $enumClass
     *            The \BackedEnum class to uses for items.
     *            It may be a string class name or a \BackedEnum instance.
     * @return \Time2Split\Help\Set A new Set.
     */
    public static function ofBackedEnum($enumClass = \BackedEnum::class)
    {
        if (! \is_a($enumClass, \BackedEnum::class, true))
            throw new \InvalidArgumentException("$enumClass must be a \BackedEnum");

        return self::toArrayKeys(function (\BackedEnum $enum) use ($enumClass) {

            if (! $enum instanceof $enumClass)
                throw new \InvalidArgumentException(sprintf('Enum must be of type %s, have %s', $enumClass, \get_class($enum)));

            return $enum->value;
        }, $enumClass::from(...));
    }

    /**
     * Decorate a set to be unmodifiable.
     *
     * Call to a mutable method will throws a {@link UnmodifiableSetException}.
     *
     * @param Set $set
     *            A set to decorate.
     * @return Set The unmodifiable set.
     */
    public static function unmodifiable(Set $set): Set
    {
        return new class($set) extends SetDecorator {

            public function offsetSet(mixed $offset, mixed $value): void
            {
                throw new UnmodifiableSetException();
            }
        };
    }

    private static Set $null;

    /**
     * Get the null pattern unmodifiable set.
     *
     * @return Set The unique null pattern set.
     */
    public static function null(): Set
    {
        return self::$null ??= new class() extends Set implements \IteratorAggregate {

            private readonly \Iterator $iterator;

            public function __construct()
            {
                $this->iterator = new \EmptyIterator();
            }

            public function offsetGet(mixed $offset): bool
            {
                return false;
            }

            public function count(): int
            {
                return 0;
            }

            public function offsetSet(mixed $offset, mixed $value): void
            {
                throw new UnmodifiableSetException();
            }

            public function getIterator(): \Traversable
            {
                return $this->iterator;
            }
        };
    }
}
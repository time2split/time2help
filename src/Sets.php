<?php

declare(strict_types=1);

namespace Time2Split\Help;

use Time2Split\Help\Classes\NotInstanciable;
use Time2Split\Help\Exception\UnmodifiableSetException;
use Time2Split\Help\_private\Set\SetDecorator;

/**
 * Factories and functions on set.
 * 
 * @package time2help\container
 * @author Olivier Rodriguez (zuri)
 */
final class Sets
{
    use NotInstanciable;

    /**
     * Provides a set storing items as array keys.
     *
     * This set is only convenient for data types that can fit as array keys.
     *
     * @return Set<string|int> A new set.
     */
    public static function arrayKeys(): Set
    {
        return new class() extends Set implements \IteratorAggregate
        {

            /**
             * @var array<string|int,bool>
             */
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
                if (!\is_bool($value))
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
     * Gets a self::arrayKeys() able to store arbitrary elements
     * as long as an element can be associated to a unique array key identifier.
     *
     * This class permits to handle more types of values and not just array keys.
     * It makes a bijection between a valid array key and an element.
     *
     * @param \Closure $toKey
     *            Map an input item to a valid key.
     * @param \Closure $fromKey
     *            Retrieves the base object from the array key.
     * @return Set<mixed> A new Set.
     */
    public static function toArrayKeys(\Closure $toKey, \Closure $fromKey): Set
    {
        /** @extends SetDecorator<int|string,mixed> */
        return new class(self::arrayKeys(), $toKey, $fromKey) extends SetDecorator
        {
            /**
             * @param Set<string|int> $decorate
             */
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
     * A set able to store \BackedEnum instances.
     *
     * @template T of \BackedEnum
     * @param string|T $enumClass
     *            The backed enum class of the elements to store.
     *            It may be a string class name of T or a T instance.
     * @return Set<T> A new Set.
     * @link https://www.php.net/manual/en/class.backedenum.php \BackedEnum
     */
    public static function ofBackedEnum($enumClass = \BackedEnum::class)
    {
        if (!\is_a($enumClass, \BackedEnum::class, true))
            throw new \InvalidArgumentException("$enumClass must be a \BackedEnum");

        /** @var Set<T> */
        return self::toArrayKeys(function (\BackedEnum $enum) use ($enumClass) {

            if (!$enum instanceof $enumClass)
                throw new \InvalidArgumentException(sprintf(
                    'Enum must be of type %s, have %s',
                    \is_string($enumClass) ? $enumClass : \get_class($enumClass),
                    \get_class($enum)
                ));

            return $enum->value;
        }, $enumClass::from(...));
    }

    /**
     * Decorates a set to be unmodifiable.
     *
     * Call to a mutable method of the set will throws a {@see Exception\UnmodifiableSetException}.
     *
     * @template T
     * @param Set<T> $set
     *            A set to decorate.
     * @return Set<T> The unmodifiable set.
     */
    public static function unmodifiable(Set $set): Set
    {
        return new class($set) extends SetDecorator
        {

            public function offsetSet(mixed $offset, mixed $value): void
            {
                throw new UnmodifiableSetException();
            }
        };
    }

    /**
     * @var Set<void>
     */
    private static Set $null;

    /**
     * Gets the null pattern unmodifiable set.
     *
     * The value is a singleton and may be compared with the === operator.
     * 
     * @return Set<void> The unique null pattern set.
     */
    public static function null(): Set
    {
        return self::$null ??= new class() extends Set implements \IteratorAggregate
        {

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

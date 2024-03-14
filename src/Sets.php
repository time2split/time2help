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
}
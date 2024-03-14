<?php
declare(strict_types = 1);
namespace Time2Split\Help;

use Time2Split\Help\Classes\NotInstanciable;
use Traversable;

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
        return new class() implements Set, \IteratorAggregate {

            private array $storage = [];

            public function offsetGet(mixed $offset): bool
            {
                return $this->storage[$offset] ?? false;
            }

            public function offsetExists(mixed $offset): bool
            {
                return isset($this->storage[$offset]);
            }

            public function offsetUnset(mixed $offset): void
            {
                unset($this->storage[$offset]);
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
                    unset($this[$offset]);
            }

            public function getIterator(): \Traversable
            {
                return new \ArrayIterator(\array_keys($this->storage));
            }
        };
    }
}
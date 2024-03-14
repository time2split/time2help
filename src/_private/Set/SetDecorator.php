<?php
declare(strict_types = 1);
namespace Time2Split\Help\_private\Set;

use Time2Split\Help\Set;

/**
 *
 * @internal
 * @author Olivier Rodriguez (zuri)
 *
 */
abstract class SetDecorator extends Set implements \IteratorAggregate
{

    public function __construct(protected readonly Set $decorate)
    {}

    public function offsetGet(mixed $offset): bool
    {
        return $this->decorate->offsetGet($offset);
    }

    public function count(): int
    {
        return $this->decorate->count();
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->decorate->offsetSet($offset, $value);
    }

    public function getIterator(): \Traversable
    {
        return $this->decorate;
    }
}
<?php

declare(strict_types=1);

namespace Time2Split\Help\_private\Set;

use Time2Split\Help\Set;

/**
 * @internal
 * 
 * @template D
 * @template T
 * @extends BaseSet<T>
 * @implements \IteratorAggregate<T>
 * @author Olivier Rodriguez (zuri)
 */
abstract class SetDecorator extends BaseSet implements \IteratorAggregate
{

    /**
     * @param Set<D> $decorate
     */
    public function __construct(protected readonly Set $decorate) {}

    public function offsetGet($offset): bool
    {
        return $this->decorate->offsetGet($offset);
    }

    public function count(): int
    {
        return $this->decorate->count();
    }

    public function offsetSet($offset,  $value): void
    {
        $this->decorate->offsetSet($offset, $value);
    }

    public function getIterator(): \Traversable
    {
        return $this->decorate;
    }
}

<?php

declare(strict_types=1);

namespace Time2Split\Help\_private\Set;

use Time2Split\Help\Set;

/**
 * Set implementation with the utility methods.
 *
 * This implementation is common to all BaseSet instances provided by the library.
 * 
 * The class {@see Sets} provides static factory methods to create instances of {@see Set}.
 * 
 * @template T
 * @implements Set<T>
 * 
 * @internal
 * @author Olivier Rodriguez (zuri)
 */
abstract class BaseSet implements Set
{

    /**
     * @param T $item $item
     */
    public final function offsetUnset($item): void
    {
        $this->offsetSet($item, false);
    }

    /**
     * @param T $item $item
     */
    public final function offsetExists($item): bool
    {
        return $this->offsetGet($item);
    }

    public final function setMore(...$items): static
    {
        foreach ($items as $item)
            $this->offsetSet($item, true);
        return $this;
    }

    public final function unsetMore(...$items): static
    {
        foreach ($items as $item)
            $this->offsetUnset($item);
        return $this;
    }

    public final function setFromList(iterable ...$lists): static
    {
        foreach ($lists as $items) {
            foreach ($items as $item)
                $this->offsetSet($item, true);
        }
        return $this;
    }

    public final function unsetFromList(iterable ...$lists): static
    {
        foreach ($lists as $items) {
            foreach ($items as $item)
                $this->offsetUnset($item);
        }
        return $this;
    }
}

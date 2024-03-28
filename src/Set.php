<?php

declare(strict_types=1);

namespace Time2Split\Help;

/**
 * Extends BaseSet with utility methods.
 *
 * 
 * The class {@see Sets} provides static factory methods to create instances of {@see Set}.
 * 
 * @template T
 * @implements BaseSet<T>
 * 
 * @package time2help\container
 * @author Olivier Rodriguez (zuri)
 */
abstract class Set implements BaseSet
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

    /**
     * Assign multiple items.
     *
     * @param T ...$items
     *            Items to assign.
     * @return static This set.
     */
    public final function setMore(...$items): static
    {
        foreach ($items as $item)
            $this->offsetSet($item, true);
        return $this;
    }

    /**
     * Drop multiple items.
     *
     * @param T ...$items
     *            Items to drop.
     * @return static This set.
     */
    public final function unsetMore(...$items): static
    {
        foreach ($items as $item)
            $this->offsetUnset($item);
        return $this;
    }

    /**
     * Assign multiple items from multiple lists.
     *
     * @param iterable<T> ...$lists
     *            Lists of items to drop.
     * @return static This set.
     */
    public final function setFromList(iterable ...$lists): static
    {
        foreach ($lists as $items) {
            foreach ($items as $item)
                $this->offsetSet($item, true);
        }
        return $this;
    }

    /**
     * Drop multiples items from multiple lists.
     *
     * @param iterable<T> ...$lists
     *            Lists of items to drop.
     * @return static This set.
     */
    public final function unsetFromList(iterable ...$lists): static
    {
        foreach ($lists as $items) {
            foreach ($items as $item)
                $this->offsetUnset($item);
        }
        return $this;
    }
}

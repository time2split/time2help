<?php
namespace Time2Split\Help;

/**
 * Extends BaseSet with utility methods.
 *
 * The class {@see Sets} provides static factory methods to create instances of {@see Set}.
 * 
 * @package time2help\container
 * @author Olivier Rodriguez (zuri)
 */
abstract class Set implements BaseSet
{

    /**
     * Drop an item.
     * 
     * @param mixed $item An item.
     */
    public final function offsetUnset(mixed $item): void
    {
        $this->offsetSet($item, false);
    }

    /**
     * Check that an item is assigned to the set.
     * 
     * @param mixed $item An item.
     * @return bool true if the value is assigned, or false if not.
     */
    public final function offsetExists(mixed $item): bool
    {
        return $this->offsetGet($item);
    }

    /**
     * Assign multiple items.
     *
     * @param mixed ...$items
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
     * @param mixed ...$items
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
     * @param iterable $items
     *            Items to set.
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
     * @param iterable $items
     *            Items to drop.
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
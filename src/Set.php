<?php
namespace Time2Split\Help;

/**
 * Extends {@link Set} with utility methods.
 *
 * @author Olivier Rodriguez (zuri)
 */
abstract class Set implements BaseSet
{

    public final function offsetUnset(mixed $offset): void
    {
        $this->offsetSet($offset, false);
    }

    public final function offsetExists(mixed $offset): bool
    {
        return $this->offsetGet($offset);
    }

    /**
     * Set multiples items.
     *
     * @param mixed ...$items
     *            Items to set.
     * @return static This set.
     */
    public final function setMore(...$items): static
    {
        foreach ($items as $item)
            $this->offsetSet($item, true);
        return $this;
    }

    /**
     * Unset multiples items.
     *
     * @param mixed ...$items
     *            Items to unset.
     * @return static This set.
     */
    public final function unsetMore(...$items): static
    {
        foreach ($items as $item)
            $this->offsetUnset($item);
        return $this;
    }

    /**
     * Set multiples items from multiple lists.
     *
     * @param iterable $items
     *            Items to set.
     * @return static This set.
     */
    public final function setFromList(iterable ...$lists): static
    {
        foreach ($lists as $items)
            foreach ($items as $item)
                $this->offsetSet($item, true);
        return $this;
    }

    /**
     * Unset multiples items from multiple lists.
     *
     * @param iterable $items
     *            Items to unset.
     * @return static This set.
     */
    public final function unsetFromList(iterable ...$lists): static
    {
        foreach ($lists as $items)
            foreach ($items as $item)
                $this->offsetUnset($item);
        return $this;
    }
}
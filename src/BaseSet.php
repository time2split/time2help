<?php
namespace Time2Split\Help;

/**
 * A set data-structure to store some types of items once.
 *
 * @author Olivier Rodriguez (zuri)
 */
interface BaseSet extends \ArrayAccess, \Countable, \Traversable
{

    /**
     *
     * {@inheritdoc}
     * @return bool <code>true</code> if the value is present, <code>false</code> if not.
     * @see \ArrayAccess::offsetGet()
     */
    public function offsetGet(mixed $offset): bool;

    /**
     *
     * {@inheritdoc}
     *
     * @param bool $value
     *            <code>true</code> to add the offset as a set item, or <code>false</code> to unset it.
     * @see \ArrayAccess::offsetSet()
     */
    public function offsetSet($offset, $value): void;
}
<?php
namespace Time2Split\Help;

/**
 * A set data-structure to store elements.
 * 
 * An element can only be assigned once in a set.
 * The comparison operation to check if two elements are equals depends on the set implementation.
 *
 * This library always provides implementations extending the abstract class {@see Set}.
 * The class {@see Sets} provides static factory methods to create instances of {@see Set}.
 * 
 * @package time2help\container
 * @author Olivier Rodriguez (zuri)
 */
interface BaseSet extends \ArrayAccess, \Countable, \Traversable
{

    /**
     * Check if an item is assigned to the set.
     * 
     * @param mixed $item An item.
     * @return bool true if the value is assigned, or false if not.
     * @link https://www.php.net/manual/en/arrayaccess.offsetget.php ArrayAccess::offsetGet()
     */
    public function offsetGet(mixed $item): bool;

    /**
     * Assign or drop an item.
     * 
     * @param mixed $item An item.
     * @param bool $value true to add the item, or false to drop it.
     * @link https://www.php.net/manual/en/arrayaccess.offsetset.php ArrayAccess::offsetSet()
     */
    public function offsetSet($item, $value): void;
}
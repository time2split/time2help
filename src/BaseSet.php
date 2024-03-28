<?php

declare(strict_types=1);

namespace Time2Split\Help;

/**
 * A set data-structure to store elements of type T.
 * 
 * An element can only be assigned once in a Baseset.
 * The comparison operation to check if two elements are equals depends on the set implementation.
 * 
 * A Baseset provides the array syntax facilities to query and modify the set contents,
 * however its only facilities: a set must not be considered as an array.
 * A Baseset<T> stores instances of T, there is no key semantic.
 *
 * This library always provides implementations of BaseSet extending the abstract class {@see Set}.
 * The class {@see Sets} provides static factory methods to create instances of {@see Set}.
 * 
 * @template T
 * @extends \ArrayAccess<T,bool>
 * @extends \Traversable<T>
 * 
 * @package time2help\container
 * @author Olivier Rodriguez (zuri)
 */
interface BaseSet extends \ArrayAccess, \Countable, \Traversable
{

    /**
     * Check if an item is assigned to the set.
     * 
     * @param T $item An item.
     * @return bool true if the value is assigned, or false if not.
     * @link https://www.php.net/manual/en/arrayaccess.offsetget.php ArrayAccess::offsetGet()
     */
    public function offsetGet($item): bool;

    /**
     * Assign or drop an item.
     * 
     * @param T $item An item.
     * @param bool $value true to add the item, or false to drop it.
     * @link https://www.php.net/manual/en/arrayaccess.offsetset.php ArrayAccess::offsetSet()
     */
    public function offsetSet($item, $value): void;

    /**
     * Drop an item.
     * 
     * @param T $item An item.
     * @link https://www.php.net/manual/en/arrayaccess.offsetunset.php ArrayAccess::offsetUnset()
     */
    public function offsetUnset($item): void;

    /**
     * Whether an item exists.
     * 
     * @param T $item An item.
     * @link https://www.php.net/manual/en/arrayaccess.offsetexists.php ArrayAccess::offsetExists()
     */
    public function offsetExists($item): bool;
}

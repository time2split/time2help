<?php

declare(strict_types=1);

namespace Time2Split\Help;

/**
 * A set data-structure to store elements without duplicates.
 * 
 * Each element in a set is unique according to a comparison operation.
 * The comparison operation depends on the implementation of the set.
 * 
 * A base set uses the array syntax to query and modify its contents,
 * however the array syntax is only provided for facilities:
 * a set can never be considered as an array.
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
     * Whether an item is assigned to the set.
     * 
     * @param T $item An item.
     * @return bool true if the value is assigned, or false if not.
     * @link https://www.php.net/manual/en/arrayaccess.offsetget.php ArrayAccess::offsetGet()
     */
    public function offsetGet($item): bool;

    /**
     * Assigns or drops an item.
     * 
     * @param T $item An item.
     * @param bool $value true to add the item, or false to drop it.
     * @link https://www.php.net/manual/en/arrayaccess.offsetset.php ArrayAccess::offsetSet()
     */
    public function offsetSet($item, $value): void;

    /**
     * Drops an item.
     * 
     * @param T $item An item.
     * @link https://www.php.net/manual/en/arrayaccess.offsetunset.php ArrayAccess::offsetUnset()
     */
    public function offsetUnset($item): void;

    /**
     * Whether an item is assigned to the set.
     * 
     * @param T $item An item.
     * @return bool true if the value is assigned, or false if not.
     * @link https://www.php.net/manual/en/arrayaccess.offsetexists.php ArrayAccess::offsetExists()
     */
    public function offsetExists($item): bool;
}

<?php

declare(strict_types=1);

namespace Time2Split\Help;

use ArrayAccess;

/**
 * Functions on arrays.
 * 
 * @package time2help\container
 * @author Olivier Rodriguez (zuri)
 */
final class Arrays
{
    use Classes\NotInstanciable;

    /**
     * Ensure that a data is an array, or wrap it inside an array.
     * 
     * @param mixed $element A data.
     * @return mixed[] $element if it is an array, or [ $element ].
     */
    public static function ensureArray($element): array
    {
        if (\is_array($element))
            return $element;

        return [$element];
    }

    /**
     * Ensure that a data is usable as an array, or wrap it inside an array.
     * 
     * @param mixed $element A data.
     * @return mixed[]|\ArrayAccess<mixed,mixed> $element if it is usable as an array, or [ $element ].
     */
    public static function ensureArrayAccess($element): array|\ArrayAccess
    {
        if (\is_array($element) || $element instanceof \ArrayAccess)
            return $element;

        return [$element];
    }

    // ========================================================================


    /**
     * Iterate through the keys.
     * 
     * @param mixed[] $array An array.
     * @return \Iterator<int,int|string> An iterator.
     */
    public static function keys(array $array): \Iterator
    {
        foreach ($array as $k => $notUsed)
            yield $k;
    }

    /**
     * Iterate through the values.
     * 
     * @template V
     * @param V[] $array An array.
     * @return \Iterator<int,V> An iterator.
     */
    public static function values(array $array): \Iterator
    {
        foreach ($array as $v)
            yield $v;
    }

    /**
     * Iterate in reverse order.
     * 
     * @template V
     * @param V[] $array An array.
     * @return \Iterator<V> An iterator.
     */
    public static function reverse(array $array): \Iterator
    {
        for (end($array); ($k = key($array)) !== null; prev($array))
            // Impossible to be a false error value since $k !== null
            yield $k =>  current($array);
    }

    /**
     * Iterate through the keys in reverse order.
     * 
     * @template V
     * @param V[] $array An array.
     * @return \Iterator<int,string|int> An iterator.
     */
    public static function reverseKeys(array $array): \Iterator
    {
        for (end($array); ($k = key($array)) !== null; prev($array))
            yield $k;
    }

    /**
     * Iterate through the value in reverse order.
     * 
     * @template V
     * @param V[] $array An array.
     * @return \Iterator<int,V> An iterator.
     */
    public static function reverseValues(array $array): \Iterator
    {
        for (end($array); ($k = key($array)) !== null; prev($array))
            // Impossible to be a false error value since $k !== null
            yield current($array);
    }

    /**
     * Iterate through each entry reversing its key and its value (ie: $val => $key).
     * 
     * @template V
     * @param V[] $array An array.
     * @return \Iterator<V,string|int> An iterator.
     */
    public static function flip(array $array): \Iterator
    {
        foreach ($array as $k => $v)
            yield $v => $k;
    }

    /**
     * Iterate through the flipped entries in reverse order.
     * 
     * @template V
     * @param V[] $array An array.
     * @return \Iterator<V,string|int> An iterator.
     * @see Arrays::flip()
     */
    public static function reverseFlip(array $array): \Iterator
    {
        for (end($array); ($k = key($array)) !== null; prev($array))
            // Impossible to be a false error value since $k !== null
            yield current($array) => $k;
    }

    /**
     * Iterate through the first array entry.
     * 
     * @template V
     * @param V[] $array An array.
     * @return \Iterator<V> An iterator on the first entry.
     */
    public static function first(array $array): \Iterator
    {
        if (empty($array))
            return;

        $k = \array_key_first($array);
        yield $k => $array[$k];
    }

    /**
     * Iterate through the last array entry.
     * 
     * @template V
     * @param V[] $array An array.
     * @return \Iterator<V> An iterator on the last entry.
     */
    public static function last(array $array): \Iterator
    {
        if (empty($array))
            return;

        $k = \array_key_last($array);
        yield $k => $array[$k];
    }

    // ========================================================================

    /**
     * Get the first key.
     * 
     * @template V
     * @param V[] $array An array.
     * @param mixed $default A default value.
     * @return mixed The first key, or $default if $array is empty.
     */
    public static function firstKey(array $array, $default = null): mixed
    {
        if (empty($array))
            return $default;

        return \array_key_first($array);
    }

    /**
     * Get the first value.
     * 
     * @template V
     * @param V[] $array An array.
     * @param mixed $default A default value.
     * @return mixed The first value, or $default if $array is empty.
     */
    public static function firstValue(array $array, $default = null): mixed
    {
        if (empty($array))
            return $default;

        return $array[\array_key_first($array)];
    }

    /**
     * Get the last key.
     * 
     * @template V
     * @param V[] $array An array.
     * @param mixed $default A default value.
     * @return mixed The last key, or $default if $array is empty.
     */
    public static function lastKey(array $array, $default = null): mixed
    {
        if (empty($array))
            return $default;

        return \array_key_last($array);
    }

    /**
     * Get the last value.
     * 
     * @template V
     * @param V[] $array An array.
     * @param mixed $default A default value.
     * @return mixed The last value, or $default if $array is empty.
     */
    public static function lastValue(array $array, $default = null): mixed
    {
        if (empty($array))
            return $default;

        return $array[\array_key_last($array)];
    }

    // ========================================================================

    /**
     * Select a part of an array.
     * 
     * @template V
     * @template D
     * @param V[] $array An array.
     * @param (string|int)[] $keys The keys of $array to select.
     * @param D $default A default value.
     * @return (D|V)[] The entries ($k => $v) of $array which their key $k is in $keys,
     *  or ($k => $default) if $k is not a key of $array.
     */
    public static function arraySelect(array $array, array $keys, $default = null): array
    {
        $ret = [];

        foreach ($keys as $k)
            $ret[$k] = $array[$k] ?? $default;

        return $ret;
    }

    // ========================================================================

    /**
     * Map then merge.
     * 
     * @param \Closure $callback A callable to run for each element in each array.
     * @param mixed[] $array An array to run through the callback function.
     * @param mixed[] ...$arrays
     *  Supplementary variable list of array arguments to run through the callback function.
     * @return mixed[] \array_merge(...\array_map($callback, $array, ...$arrays))
     * 
     * @see https://www.php.net/manual/en/function.array-map.php
     * @see https://www.php.net/manual/en/function.array-merge.php
     */
    public static function arrayMapMerge(\Closure $callback, array $array, array ...$arrays): array
    {
        return \array_merge(...\array_map($callback, $array, ...$arrays));
    }

    /**
     * Map then deduplicate elements.
     * 
     * @param \Closure $callback A callable to run for each element in each array.
     * @param mixed[] $array An array to run through the callback function.
     * @param int $flags 
     * The optional second parameter flags may be used to modify the comparison behavior using these values:
     * 
     * Comparison type flags:
     * - SORT_REGULAR - compare items normally (don't change types)
     * - SORT_NUMERIC - compare items numerically
     * - SORT_STRING - compare items as strings
     * - SORT_LOCALE_STRING - compare items as strings, based on the current locale.
     * 
     * @return mixed[] \array_merge(...\array_map($callback, $array))
     * 
     * @see https://www.php.net/manual/en/function.array-map.php
     * @see https://www.php.net/manual/en/function.array-unique.php
     */
    public static function arrayMapUnique(\Closure $callback, array $array, int $flags = SORT_REGULAR): array
    {
        return \array_unique(\array_map($callback, $array), $flags);
    }

    /**
     * Applies a callback to the keys of a given array.
     * 
     * @param \Closure $callback A closure to run for each key of the array.
     * @param mixed[] $array An array.
     * @return mixed[] An array where each entry ($k => $v) has been replaced by ($callback($k) => $v).
     */
    public static function arrayMapKey(\Closure $callback, array $array): array
    {
        return \array_combine(\array_map($callback, \array_keys($array)), $array);
    }

    /**
     * Partitions an array in two according to a filter.
     * 
     * @param mixed[] $array An array.
     * @param \Closure $filter A filter to apply on each entry of the array.
     *  If no callback is supplied, all empty entries of array will be removed.
     * See empty() for how PHP defines empty in this case.
     * @param int $mode Flag determining what arguments are sent to callback:
     *  - ARRAY_FILTER_USE_KEY - pass key as the only argument to callback instead of the value
     *  - ARRAY_FILTER_USE_BOTH - pass both value and key as arguments to callback instead of the value
     *
     * Default is 0 which will pass value as the only argument to callback instead.
     * @return array<mixed[]> A list of two arrays where $list[0] are the entries validated by the filter
     *  and $list[1] are the remaining entries not filtered.
     */
    public static function arrayPartition(array $array, ?\Closure $filter, int $mode = 0): array
    {
        $a = \array_filter($array, $filter, $mode);
        $b = \array_diff_key($array, $a);
        return [
            $a,
            $b
        ];
    }

    // ========================================================================
    // UPDATE
    // ========================================================================

    /**
     * Updates some entries in an array using callbacks.
     *  
     * @param mixed[] &$array A reference to an array to update.
     * @param iterable<mixed> $args The updated ($k => $v) entries to set in the array. 
     * @param ?\Closure $onExists
     *  - $onUnexists($k,$v,&$array):void
     * 
     *  Updates an existant entry in array.
     *  If null then an \Exception is thrown for the first existant key entry met.
     * @param ?\Closure $onUnexists
     *  - $onUnexists($k,$v,&$array):void
     * 
     *  Updates a non existant entry in array.
     *  If null then an \Exception is thrown for the first unexistant key entry met.
     * @param \Closure $mapKey
     *  - $mapKey($key):int|string
     * 
     *  If set then transform each $args entry to ($mapKey($k) => $v).
     */
    public static function updateWithClosures(
        array &$array,
        iterable $args,
        ?\Closure $onExists = null,
        ?\Closure $onUnexists = null,
        ?\Closure $mapKey = null,
    ): void {
        if ($onUnexists === null)
            $onUnexists = fn ($k, $v, $array) => throw new \Exception("The key '$k' does not exists in the array: " . implode(',', \array_keys($array)));
        if ($onExists === null)
            $onExists = fn ($k, $v, $array) => throw new \Exception("The key '$k' already exists in the array: " . implode(',', \array_keys($array)));
        if (null === $mapKey)
            $mapKey = fn ($k) => $k;
        foreach ($args as $k => $v) {
            $k = $mapKey($k);

            if (!\array_key_exists($k, $array))
                $onUnexists($k, $v, $array);
            else
                $onExists($k, $v, $array);
        }
    }

    /**
     * @param mixed[] $array
     */
    private static function updateEntry(string|int $k, mixed $v, array &$array): void
    {
        $array[$k] = $v;
    }

    /**
     * Updates entries in an array and add the unexistant ones.
     * 
     * @param mixed[] &$array A reference to an array to update.
     * @param iterable<mixed> $args The entries to update.
     * @param \Closure $mapKey
     *  - $mapKey($key):int|string
     * 
     *  If set then transform each $args entry to ($mapKey($k) => $v).
     */
    public static function update(
        array &$array,
        iterable $args,
        ?\Closure $mapKey = null,
    ): void {
        self::updateWithClosures($array, $args, self::updateEntry(...), self::updateEntry(...), $mapKey);
    }

    /**
     * Updates some existant entries in an array and return the non existant ones.
     * 
     * @param mixed[] &$array A reference to an array to update.
     * @param iterable<mixed> $args The updated ($k => $v) entries to set in the array. 
     * @param \Closure $mapKey
     *  - $mapKey($key):int|string
     * 
     *  If set then transform each $args entry to ($mapKey($k) => $v).
     * @return mixed[] The updated entries of $args that didn't exists in $array.
     */
    public static function updateIfPresent(
        array &$array,
        iterable $args,
        ?\Closure $mapKey = null,
    ): array {
        $remains = [];
        $fstore = function ($k, $v) use (&$remains): void {
            $remains[$k] = $v;
        };
        self::updateWithClosures($array, $args, self::updateEntry(...), $fstore, $mapKey);
        return $remains;
    }

    /**
     * Updates the non-existant entries in an array and return the existant ones.
     * 
     * @param mixed[] &$array A reference to an array to update.
     * @param iterable<mixed> $args The updated ($k => $v) entries to set in the array. 
     * @param \Closure $mapKey
     *  - $mapKey($key):int|string
     * 
     *  If set then transform each $args entry to ($mapKey($k) => $v).
     * @return mixed[] The updated entries of $args that already exists in $array.
     */
    public static function updateIfAbsent(
        array &$array,
        iterable $args,
        ?\Closure $mapKey = null,
    ): array {
        $remains = [];
        $fstore = function ($k, $v) use (&$remains): void {
            $remains[$k] = $v;
        };
        self::updateWithClosures($array, $args, $fstore, self::updateEntry(...), $mapKey);
        return $remains;
    }

    // ========================================================================
    // REMOVE

    /**
     * Deletes an entry from an array using its key then return its value.
     * 
     * @param mixed[] &$array A reference to an array.
     * @param string|int $key The key of the entry to delete.
     * @param mixed $default The value to be returned if there is no value present.
     * @return mixed The removed value, if present, otherwise $default.
     */
    public static function removeEntry(array &$array, string|int $key, $default = null): mixed
    {
        if (!\array_key_exists($key, $array))
            return $default;

        $ret = $array[$key];
        unset($array[$key]);
        return $ret;
    }

    /**
     * Deletes some values from an array.
     * 
     * @param mixed[] &$array A reference to an array.
     * @param bool $strict If the comparison must be strict (===) or not (==).
     * @param mixed ...$vals Some values to delete.
     */
    public static function dropValues(array &$array, bool $strict, ...$vals): void
    {
        foreach ($vals as $val) {
            $k = \array_search($val, $array, $strict);

            if (false !== $k)
                unset($array[$k]);
        }
    }
    /**
     * Deletes some values from an array using the equality operator (==).
     * 
     * @param mixed[] &$array A reference to an array.
     * @param mixed ...$vals Some values to delete.
     */
    public static function dropEqualValues(array &$array, ...$vals): void
    {
        self::dropValues($array, false, ...$vals);
    }
    /**
     * Deletes some values from an array using the identity operator (===).
     * 
     * @param mixed[] &$array A reference to an array.
     * @param mixed ...$vals Some values to delete.
     */
    public static function dropIdenticalValues(array &$array, ...$vals): void
    {
        self::dropValues($array, true, ...$vals);
    }

    /**
     * Removes some entries from an array according to a filter.
     * 
     * @param mixed[] &$array A reference to an array.
     * @param \Closure $filter A filter to apply on each entry.
     *  If no callback is supplied, all empty entries of array will be removed.
     * See empty() for how PHP defines empty in this case.
     * @param int $mode Flag determining what arguments are sent to callback:
     *  - ARRAY_FILTER_USE_KEY - pass key as the only argument to callback instead of the value
     *  - ARRAY_FILTER_USE_BOTH - pass both value and key as arguments to callback instead of the value
     *
     * Default is 0 which will pass value as the only argument to callback instead.
     * @return mixed[] An array of the removed entries.
     */
    public static function removeWithFilter(array &$array, ?\Closure $filter = null, int $mode = 0): array
    {
        $drop = [];
        $ret = [];


        if ($filter === null) {
            $filter = fn ($v) => empty($v);
            $mode = 0;
        }
        if ($mode === 0)
            $fmakeParams = fn ($k, $v) => [$v];
        elseif ($mode === ARRAY_FILTER_USE_KEY)
            $fmakeParams = fn ($k, $v) => [$k];
        elseif ($mode === ARRAY_FILTER_USE_BOTH)
            $fmakeParams = fn ($k, $v) => [$v, $k];
        else
            throw new \Exception("Invalid mode $mode");

        foreach ($array as $k => $v) {
            $valid = $filter(...$fmakeParams($k, $v));

            if ($valid) {
                $drop[] = $k;
                $ret[$k] = $v;
            }
        }
        foreach ($drop as $d)
            unset($array[$d]);

        return $ret;
    }
}

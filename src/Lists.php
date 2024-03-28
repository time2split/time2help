<?php

declare(strict_types=1);

namespace Time2Split\Help;

use Time2Split\Help\Classes\NotInstanciable;
use TreeArrays;

/**
 * Functions on lists (arrays with integer keys).
 * 
 * @package time2help\container
 * @author Olivier Rodriguez (zuri)
 */
final class Lists
{
    use NotInstanciable;

    /**
     * Ensures that a value is a list.
     *
     * @param  mixed $value A value.
     * @return array<int,mixed> Transforms any array $value to \array_values($value),
     *  else return [$value].
     */
    public static function ensureList($value): array
    {
        if (\is_array($value))
            return \array_values($value);

        return [$value];
    }

    /**
     * Checks whether a given array is a list.
     * 
     * Determines if the given array is a list.
     * An array is considered a list if its keys consist of consecutive numbers from 0 to count($array)-1.
     *
     * @param  mixed[] $array The array being evaluated.
     * @return bool Returns true if array is a list, false otherwise.
     */
    public static function isList(array $array): bool
    {
        return array_is_list($array);
    }

    /**
     * Finds whether an array is almost a list.
     * 
     * An array is almost a list (or an 'almost list') if its keys are all integers.
     * Note that every list is almost a list.
     * 
     * @param mixed[] $array An array.
     * @return bool true if the array is almost a list.
     */
    public static function isAlmostList(array $array): bool
    {
        $notInt = \array_filter(\array_keys($array), fn ($k) => !\is_int($k));
        return empty($notInt);
    }

    /**
     * Transform an almost list to a list.
     * 
     * @template V
     * @param array<int,V> &$almostList An almost list.
     * @return array<int,V> A list.
     * @throws \InvalidArgumentException if $almostList is not an almost list.
     */
    public static function almostListToList(array $almostList): array
    {
        if (!self::isAlmostList($almostList))
            throw new \InvalidArgumentException('The argument must be an almost list');

        return \array_values($almostList);
    }

    /**
     * Transform an almost list to a list.
     * 
     * @param array<mixed> &$array An almost list.
     * @param \Closure $supplyOnFailure Callback that supply the value to return if $array is not an almost list.
     * @return mixed A list, or $supplyOnFailure() if $array is not an almost list.
     * @throws \InvalidArgumentException if $array is not an almost list and $supplyOnFailure is not set.
     */
    public static function tryAlmostListToList(array $array, \Closure $supplyOnFailure = null): mixed
    {
        if (!self::isAlmostList($array)) {

            if (isset($supplyOnFailure))
                return $supplyOnFailure();
            else
                throw new \InvalidArgumentException('The argument must be an almost list');
        } else
            return \array_values($array);
    }

    // ========================================================================
    // UPDATE
    // ========================================================================

    /**
     * Change an almost list to be a list.
     * 
     * If the array is not an almost list then it does nothing.
     * 
     * @param array<int,mixed> &$array A reference to an array.
     */
    public static function muteToList(array &$array): void
    {
        if (self::isAlmostList($array))
            $array =  \array_values($array);
    }

    /**
     * Reindex every almost list of the array, including the array iteself, to be a list.
     * 
     * @param array<mixed> &$array A reference to an array.
     */
    public static function muteToListRecursive(array &$array): void
    {
        TreeArrays::walk_depth($array, function (&$val) {
            if (\is_array($val))
                self::muteToList($val);
        });
    }
}

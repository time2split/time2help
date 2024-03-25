<?php
declare(strict_types=1);
namespace Time2Split\Help;

use Time2Split\Help\Classes\NotInstanciable;

final class Traversables
{
    use NotInstanciable;

    public static function count(iterable $sequence): int
    {
        if (\is_array($sequence) || $sequence instanceof \Countable)
            return \count($sequence);

        $i = 0;
        foreach ($sequence as $NotUsed)
            $i++;
        return $i;
    }

    // ========================================================================
    /**
     * Iterate through the keys.
     */
    public static function keys(iterable $sequence): \Iterator
    {
        if (\is_array($sequence))
            yield from Arrays::keys($sequence);
        else
            foreach ($sequence as $k => $notUsed) {
                yield $k;
            }
    }

    /**
     * Iterate through the values.
     */
    public static function values(iterable $sequence): \Iterator
    {
        if (\is_array($sequence))
            yield from Arrays::values($sequence);
        else
            foreach ($sequence as $v)
                yield $v;
    }

    /**
     * Iterate in reverse order.
     */
    public static function reverse(iterable $array): \Iterator
    {
        if (!\is_array($array))
            $array = \iterator_to_array($array);

        return Arrays::reverse($array);
    }

    /**
     * Iterate through the keys in reverse order.
     */
    public static function reverseKeys(iterable $array): \Iterator
    {
        if (!\is_array($array))
            $array = \iterator_to_array($array);

        return Arrays::reverseKeys($array);
    }

    /**
     * Iterate through the values in reverse order.
     */
    public static function reverseValues(iterable $array): \Iterator
    {
        if (!\is_array($array))
            $array = \iterator_to_array($array);

        return Arrays::reverseValues($array);
    }

    /**
     * Iterate through each entry reversing its key and its value (ie: $val => $key).
     */
    public static function flip(iterable $sequence, $default = null): \Iterator
    {
        if (\is_array($sequence))
            yield from Arrays::flip($sequence);
        else
            foreach ($sequence as $k => $v)
                yield $v => $k;
    }
    /**
     * Iterate through the flipped entries in reverse order.
     * @see Traversables::flip()
     */
    public static function reverseFlip(iterable $sequence): \Iterator
    {
        if (\is_array($sequence))
            return Arrays::reverseFlip($sequence);

        return self::flip(self::reverse($sequence));
    }

    // ========================================================================

    /**
     * Get the first key.
     */
    public static function firstKey(iterable $sequence, $default = null): mixed
    {
        if (\is_array($sequence))
            return Arrays::firstKey($sequence);

        foreach ($sequence as $k => $NotUsed)
            return $k;

        return $default;
    }

    /**
     * Get the first value.
     */
    public static function firstValue(iterable $sequence, $default = null): mixed
    {
        if (\is_array($sequence))
            return Arrays::firstValue($sequence);

        foreach ($sequence as $v)
            return $v;

        return $default;
    }

    /**
     * Get the last key.
     */
    public static function lastKey(iterable $sequence, $default = null): mixed
    {
        if (\is_array($sequence))
            return Arrays::lastKey($sequence);

        $k = $default;

        foreach ($sequence as $k => $NotUsed)
            ;
        return $k;
    }

    /**
     * Get the last value.
     */
    public static function lastValue(iterable $sequence, $default = null): mixed
    {
        if (\is_array($sequence))
            return Arrays::lastValue($sequence);

        $v = $default;

        foreach ($sequence as $v)
            ;
        return $v;
    }

    /**
     * Iterate through the first entry.
     */
    public static function first(iterable $sequence): \Iterator
    {
        if (\is_array($sequence))
            yield from Arrays::first($sequence);
        else
            foreach ($sequence as $k => $v) {
                yield $k => $v;
                return;
            }
    }

    /**
     * Iterate through the last entry.
     */
    public static function last(iterable $sequence): \Iterator
    {
        if (\is_array($sequence))
            yield from Arrays::last($sequence);
        else {
            foreach ($sequence as $k => $v)
                ;
            yield $k => $v;
        }
    }

    // ========================================================================
    /**
     * Apply closures to each key and value from entries.
     * 
     * @param iterable $sequence A sequence of entries.
     * @param \Closure $mapKey A closure to apply on keys.
     * @param \Closure $mapValue A closure to apply on values.
     * @return \Iterator An iterator on the mapped entries.
     */
    public static function map(iterable $sequence, \Closure $mapKey, \Closure $mapValue): \Iterator
    {
        foreach ($sequence as $k => $v)
            yield $mapKey($k) => $mapValue($v);
    }


    /**
     * Apply a closure on each key.
     * 
     * @param iterable $sequence A sequence of entries.
     * @param \Closure $mapKey A closure to apply on keys.
     * @return \Iterator An iterator on the mapped entries.
     */
    public static function mapKey(iterable $sequence, \Closure $mapKey): \Iterator
    {
        foreach ($sequence as $k => $v)
            yield $mapKey($k) => $v;
    }

    /**
     * Apply a closure on each value.
     * 
     * @param iterable $sequence A sequence of entries.
     * @param \Closure $mapValue A closure to apply on values.
     * @return \Iterator An iterator on the mapped entries.
     */
    public static function mapValue(iterable $sequence, \Closure $mapValue): \Iterator
    {
        foreach ($sequence as $k => $v)
            yield $k => $mapValue($v);
    }

    // ========================================================================

    /**
     * Iterate through a slice of an iterable.
     * 
     * @param iterable $sequence A sequence of entries.
     * @param int $offset A positive offset from wich to begin.
     * @param mixed $length A positive length of the number of entries to read.
     * @return \Iterator An iterator of the selected slice.
     * @throws \DomainException If the offset or the length is negative.
     */
    public static function limit(iterable $sequence, int $offset = 0, int $length = null): \Iterator
    {
        if ($offset < 0)
            throw new \DomainException("The offset must be positive, has $offset");
        if ($length < 0)
            throw new \DomainException("The offset must be positive, has $length");
        if ($length === 0)
            return;

        $i = 0;

        if ($offset === 0) {

            if (null === $length) {

                foreach ($sequence as $k => $v)
                    yield $k => $v;
            } else {

                foreach ($sequence as $k => $v) {
                    yield $k => $v;

                    if (--$length === 0)
                        return;
                }
            }
        } elseif (null === $length) {

            foreach ($sequence as $k => $v) {

                if ($i === $offset)
                    yield $k => $v;
                else
                    $i++;
            }
        } else {

            foreach ($sequence as $k => $v) {

                if ($i === $offset) {
                    yield $k => $v;

                    if (--$length === 0)
                        return;
                } else
                    $i++;
            }
        }
    }
}
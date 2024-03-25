<?php
declare(strict_types=1);
namespace Time2Split\Help;

use Time2Split\Help\Classes\NotInstanciable;

/**
 * Functions on iterables.
 * 
 * @author Olivier Rodriguez (zuri)
 */
final class Iterables
{
    use NotInstanciable;

    /**
     * Ensure that an iterable is rewindable.
     * 
     * @param iterable $array An iterable.
     * @param bool $anIteratorIsRewritable
     *      true if the sent $array is a rewindable iterable or a \Generator.
     * @return \Iterator Return a rewindable iterator.
     */
    public static function ensureRewindableIterator(iterable $array, bool $iteratorClassIsRewindable = true): \Iterator
    {
        if (\is_array($array))
            return new \ArrayIterator($array);
        if ($iteratorClassIsRewindable && $array instanceof \Iterator) {

            if (!($array instanceof \Generator))
                return $array;
        }
        return new \ArrayIterator(\iterator_to_array($array));
    }

    // ========================================================================

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
    // ========================================================================
    private static function sequenceSizeIsLowerThan_mayBeStrict(bool $strict): \Closure
    {
        return $strict ? self::sequenceSizeIsStrictlyLowerThan(...) : self::sequenceSizeIsLowerOrEqual(...);
    }

    private static function sequenceSizeIsLowerOrEqual(\Iterator $a, \Iterator $b): bool
    {
        return !$a->valid();
    }

    private static function sequenceSizeIsStrictlyLowerThan(\Iterator $a, \Iterator $b): bool
    {
        return !$a->valid() && $b->valid();
    }

    private static function sequenceSizeEquals(\Iterator $a, \Iterator $b): bool
    {
        return !$a->valid() && !$b->valid();
    }

    private static function true(): \Closure
    {
        return fn () => true;
    }

    private static function equals_mayBeStrict(bool $strict): \Closure
    {
        return $strict ? fn ($a, $b) => $a === $b : fn ($a, $b) => $a == $b;
    }

    // ========================================================================
    private static function sequenceHasInclusionRelation(iterable $a, iterable $b, \Closure $keyEquals, \Closure $valueEquals, \Closure $endValidation): bool
    {
        $a = Iterables::ensureRewindableIterator($a);
        $b = Iterables::ensureRewindableIterator($b);
        $a->rewind();
        $b->rewind();

        while ($a->valid() && $b->valid()) {

            if (!$keyEquals($a->key(), $b->key()) || !$valueEquals($a->current(), $b->current()))
                return false;

            $a->next();
            $b->next();
        }
        return $endValidation($a, $b);
    }

    // ========================================================================

    /**
     * Check if two sequences are in an equal relation according to external keys and values comparison closures.
     * 
     * Two sequences are in an equal relation if they have the same key => value entries in the same order.
     * 
     * @param iterable $a A sequence of entries.
     * @param iterable $b A sequence of entries.
     * @param \Closure $keyEquals The keys comparison closure.
     * @param \Closure $valueEquals The values comparison closure.
     * @return bool true if there is an equal relation between the sequences, or else false.
     */
    public static function sequenceHasEqualRelation(iterable $a, iterable $b, \Closure $keyEquals, \Closure $valueEquals): bool
    {
        return self::sequenceHasInclusionRelation($a, $b, $keyEquals, $valueEquals, self::sequenceSizeEquals(...));
    }

    /**
     * Check if two sequences are equals using one of the php equal operator (== or ===) as keys and values comparison.
     * 
     * Two sequences are equals if they have the same key => value entries in the same order.
     * 
     * @param iterable $a A sequence of entries.
     * @param iterable $b A sequence of entries.
     * @param bool $strictKeyEquals true if the keys comparison is ===, or false for ==.
     * @param bool $strictValueEquals true if the values comparison is ===, or false for ==.
     * @return bool true if the sequences are equals, or else false.
     */
    public static function sequenceEquals(iterable $a, iterable $b, bool $strictKeyEquals = false, bool $strictValueEquals = false): bool
    {
        return self::sequenceHasEqualRelation($a, $b, self::equals_mayBeStrict($strictKeyEquals), self::equals_mayBeStrict($strictValueEquals));
    }

    /**
     * Check if a sequence is the begining of another one according to external keys and values comparison closures.
     * 
     * @param iterable $a The first sequence of entries.
     * @param iterable $b The second sequence of entries.
     * @param \Closure $keyEquals The keys comparison closure.
     * @param \Closure $valueEquals The values comparison closure.
     * @param bool $strictPrefix true if the first sequence must be smaller than the second, or false if both may have the same size.
     * @return bool true if the first sequence is a prefix of the second one, or else false.
     */
    public static function sequenceHasPrefixRelation(iterable $a, iterable $b, \Closure $keyEquals, \Closure $valueEquals, bool $strictPrefix = false): bool
    {
        return self::sequenceHasInclusionRelation($a, $b, $keyEquals, $valueEquals, self::sequenceSizeIsLowerThan_mayBeStrict($strictPrefix));
    }

    /**
     * Check if a sequence is the begining of another using one of the php equal operator (== or ===) as keys and values comparison.
     * 
     * @param iterable $a The first sequence of entries.
     * @param iterable $b The second sequence of entries.
     * @param bool $strictKeyEquals true if the keys comparison is ===, or false for ==.
     * @param bool $strictValueEquals true if the values comparison is ===, or false for ==.
     * @param bool $strictPrefix true if the first sequence must be smaller than the second, or false if both may have the same size.
     * @return bool true if the first sequence is a prefix of the second one, or else false.
     */
    public static function sequencePrefixEquals(iterable $a, iterable $b, bool $strictKeyEquals = false, $strictValueEquals = false, bool $strictPrefix = false): bool
    {
        return self::sequenceHasPrefixRelation($a, $b, self::equals_mayBeStrict($strictKeyEquals), self::equals_mayBeStrict($strictValueEquals), $strictPrefix);
    }

    // ========================================================================

    /**
     * Check if two lists are in an equal relation according to an external values comparison closure.
     *
     * Two lists are in an equal relation if they have the same values in the same order.
     * 
     * @param iterable $a A list of values.
     * @param iterable $b A list of values.
     * @param \Closure $valueEquals The values comparison closure.
     * @return bool true if there is an equal relation between the lists, or else false.
     */
    public static function listHasEqualRelation(iterable $a, iterable $b, \Closure $valueEquals): bool
    {
        return self::sequenceHasInclusionRelation($a, $b, self::true(), $valueEquals, self::sequenceSizeEquals(...));
    }

    /**
     * Check if two lists are in an equal relation using one of the php equal operator (== or ===) as keys and values comparison.
     * 
     * @param iterable $a A list of values.
     * @param iterable $b A list of values.
     * @param bool $strictEquals true if the values comparison is ===, or false for ==.
     * @return bool true if the lists are equals, or else false.
     */
    public static function listEquals(iterable $a, iterable $b, bool $strictEquals = false): bool
    {
        return self::listHasEqualRelation($a, $b, self::equals_mayBeStrict($strictEquals));
    }

    /**
     * Check if a list is the begining of another one according to external values comparison closures.
     * 
     * @param iterable $a The first list of values.
     * @param iterable $b The second list of values.
     * @param \Closure $valueEquals The values comparison closure.
     * @param bool $strictPrefix true if the first list must be smaller than the second, or false if both may have the same size.
     * @return bool true if the first list is a prefix of the second one, or else false.
     */
    public static function listHasPrefixRelation(iterable $a, iterable $b, \Closure $valueEquals, bool $strictPrefix = false): bool
    {
        return self::sequenceHasInclusionRelation($a, $b, self::true(), $valueEquals, self::sequenceSizeIsLowerThan_mayBeStrict($strictPrefix));
    }

    /**
     * Summary of listPrefixEquals
     * @param iterable $a The first list of values.
     * @param iterable $b The second list of values.
     * @param bool $strictEquals true if the values comparison is ===, or false for ==.
     * @param bool $strictPrefix true if the first list must be smaller than the second, or false if both may have the same size.
     * @return bool true if the first list is a prefix of the second one, or else false.
     */
    public static function listPrefixEquals(iterable $a, iterable $b, bool $strictEquals = false, bool $strictPrefix = false): bool
    {
        return self::listHasPrefixRelation($a, $b, self::equals_mayBeStrict($strictEquals), $strictPrefix);
    }
}
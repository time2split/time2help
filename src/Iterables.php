<?php

declare(strict_types=1);

namespace Time2Split\Help;

use Time2Split\Help\Classes\NotInstanciable;
use Traversable;

/**
 * Functions on iterables.
 *
 * @package time2help\container
 * @author Olivier Rodriguez (zuri)
 */
final class Iterables
{
    use NotInstanciable;

    /**
     * Ensures that a value is iterable like a list (ordered int keys).
     *
     * @param mixed $value A value.
     * @return array<int,mixed> Transforms any iterable<V> $value to an iterable<int,V> one,
     *  else return [$value].
     */
    public static function ensureList($value): array
    {
        if (\is_array($value))
            return Lists::ensureList($value);
        if ($value instanceof Traversable)
            self::values($value);
        return [$value];
    }

    /**
     * Ensures that a value is iterable.
     *
     * @param mixed $value A value.
     * @return iterable<mixed> Return the iterable $value, else return [$value].
     */
    public static function ensureIterable($value): iterable
    {
        if (\is_iterable($value))
            return $value;
        return [$value];
    }

    /**
     * Ensure that an iterable is rewindable.
     *
     * @template K
     * @template V
     * @param iterable<K,V> $sequence A sequence of entries.
     * @param bool $iteratorClassIsRewindable
     *      true if the sent $sequence is a rewindable iterable or a \Generator.
     * @return \Iterator<K,V> Return a rewindable iterator.
     */
    public static function ensureRewindableIterator(iterable $sequence, bool $iteratorClassIsRewindable = true): \Iterator
    {
        if (\is_array($sequence))
            return new \ArrayIterator($sequence);
        if ($iteratorClassIsRewindable && $sequence instanceof \Iterator) {

            if (!($sequence instanceof \Generator))
                return $sequence;
        }
        return new \ArrayIterator(\iterator_to_array($sequence));
    }

    // ========================================================================

    /**
     * Count the number of entries of A sequence of entries.
     *
     * @template K
     * @template V
     * @param iterable<K,V> $sequence A sequence of entries.
     * @param bool $allowCountable Allow to use \count($sequence) if the sequence is \Countable.
     * @return int The number of entries.
     */
    public static function count(iterable $sequence, bool $allowCountable = false): int
    {
        if (\is_array($sequence) || ($allowCountable && $sequence instanceof \Countable))
            return \count($sequence);

        $i = 0;
        foreach ($sequence as $NotUsed)
            $i++;
        return $i;
    }

    // ========================================================================

    /**
     * Iterate through the keys.
     *
     * @template K
     * @template V
     * @param iterable<K,V> $sequence A sequence of entries.
     * @return \Iterator<int,K>
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
     *
     * @template K
     * @template V
     * @param iterable<K,V> $sequence A sequence of entries.
     * @return \Iterator<int,V>
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
     *
     * @template K
     * @template V
     * @param iterable<K,V> $sequence A sequence of entries.
     * @return \Iterator<K,V>
     */
    public static function reverse(iterable $sequence): \Iterator
    {
        if (!\is_array($sequence))
            $sequence = \iterator_to_array($sequence);

        /** @var \Iterator<K,V> */
        return Arrays::reverse($sequence);
    }

    /**
     * Iterate through the keys in reverse order.
     *
     * @template K
     * @template V
     * @param iterable<K,V> $sequence A sequence of entries.
     * @return \Iterator<int,K>
     */
    public static function reverseKeys(iterable $sequence): \Iterator
    {
        if (!\is_array($sequence))
            $sequence = \iterator_to_array($sequence);

        /** @var \Iterator<int,K> */
        return Arrays::reverseKeys($sequence);
    }

    /**
     * Iterate through the values in reverse order.
     *
     * @template K
     * @template V
     * @param iterable<K,V> $sequence A sequence of entries.
     * @return \Iterator<int,V>
     */
    public static function reverseValues(iterable $sequence): \Iterator
    {
        if (!\is_array($sequence))
            $sequence = \iterator_to_array($sequence);

        return Arrays::reverseValues($sequence);
    }

    /**
     * Iterate through each entry reversing its key and its value (ie: $val => $key).
     *
     * @template K
     * @template V
     * @param iterable<K,V> $sequence A sequence of entries.
     * @return \Iterator<V,K>
     */
    public static function flip(iterable $sequence): \Iterator
    {
        if (\is_array($sequence))
            yield from Arrays::flip($sequence);
        else
            foreach ($sequence as $k => $v)
                yield $v => $k;
    }
    /**
     *
     * Iterate through the flipped entries in reverse order.
     *
     * @template K
     * @template V
     * @param iterable<K,V> $sequence A sequence of entries.
     * @return \Iterator<V,K>
     * @see Traversables::flip()
     */
    public static function reverseFlip(iterable $sequence): \Iterator
    {
        if (\is_array($sequence))
            /** @var \Iterator<V,K> */
            return Arrays::reverseFlip($sequence);

        return self::flip(self::reverse($sequence));
    }

    // ========================================================================

    /**
     * Get the first key.
     *
     * @template K
     * @param iterable<K,mixed> $sequence A sequence of entries.
     * @param mixed $default A default value to return.
     * @return K The first key of $sequence, or $default if the sequence is empty.
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
     *
     * @template K
     * @template V
     * @param iterable<K,V> $sequence A sequence of entries.
     * @param mixed $default A default value to return.
     * @return V The first value of $sequence, or $default if the sequence is empty.
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
     *
     * @template K
     * @param iterable<K,mixed> $sequence A sequence of entries.
     * @param mixed $default A default value to return.
     * @return K The last key of $sequence, or $default if the sequence is empty.
     */
    public static function lastKey(iterable $sequence, $default = null): mixed
    {
        if (\is_array($sequence))
            return Arrays::lastKey($sequence);

        $k = $default;

        foreach ($sequence as $k => $NotUsed);
        return $k;
    }

    /**
     * Get the last value.
     *
     * @template K
     * @template V
     * @param iterable<K,V> $sequence A sequence of entries.
     * @param mixed $default A default value to return.
     * @return V The last value of $sequence, or $default if the sequence is empty.
     */
    public static function lastValue(iterable $sequence, $default = null): mixed
    {
        if (\is_array($sequence))
            return Arrays::lastValue($sequence);

        $v = $default;

        foreach ($sequence as $v);
        return $v;
    }

    /**
     * Iterate through the first entry.
     *
     * @template K
     * @template V
     * @param iterable<K,V> $sequence A sequence of entries.
     * @return \Iterator<V> An iterator on the first entry.
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
     *
     * @template K
     * @template V
     * @param iterable<K,V> $sequence A sequence of entries.
     * @return \Iterator<V> An iterator on the last entry.
     */
    public static function last(iterable $sequence): \Iterator
    {
        if (\is_array($sequence))
            yield from Arrays::last($sequence);
        else {
            $doonce = false;

            foreach ($sequence as $k => $v)
                $doonce = true;

            if ($doonce)
                yield $k => $v;
        }
    }

    // ========================================================================
    /**
     * Apply closures to each key and value from entries.
     *
     * @template K
     * @template V
     * @param iterable<K,V> $sequence A sequence of entries.
     * @param \Closure $mapKey A closure to apply on keys.
     * @param \Closure $mapValue A closure to apply on values.
     * @return \Iterator<mixed> An iterator on the mapped entries.
     */
    public static function map(iterable $sequence, \Closure $mapKey, \Closure $mapValue): \Iterator
    {
        foreach ($sequence as $k => $v)
            yield $mapKey($k) => $mapValue($v);
    }


    /**
     * Apply a closure on each key.
     *
     * @template K
     * @template V
     * @param iterable<K,V> $sequence A sequence of entries.
     * @param \Closure $mapKey A closure to apply on keys.
     * @return \Iterator<mixed,V> An iterator on the mapped entries.
     */
    public static function mapKey(iterable $sequence, \Closure $mapKey): \Iterator
    {
        foreach ($sequence as $k => $v)
            yield $mapKey($k) => $v;
    }

    /**
     * Apply a closure on each value.
     *
     * @template K
     * @template V
     * @param iterable<K,V> $sequence A sequence of entries.
     * @param \Closure $mapValue A closure to apply on values.
     * @return \Iterator<K,mixed> An iterator on the mapped entries.
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
     * @template K
     * @template V
     * @param iterable<K,V> $sequence A sequence of entries.
     * @param int $offset A positive offset from wich to begin.
     * @param int $length A positive length of the number of entries to read.
     * @return \Iterator<K,V> An iterator of the selected slice.
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

    /**
     * @param \Iterator<mixed> $a A sequence of entries.
     * @param \Iterator<mixed> $b A sequence of entries.
     */
    private static function sequenceSizeIsLowerOrEqual(\Iterator $a, \Iterator $b): bool
    {
        return !$a->valid();
    }

    /**
     * @param \Iterator<mixed> $a A sequence of entries.
     * @param \Iterator<mixed> $b A sequence of entries.
     */
    private static function sequenceSizeIsStrictlyLowerThan(\Iterator $a, \Iterator $b): bool
    {
        return !$a->valid() && $b->valid();
    }

    /**
     * @param \Iterator<mixed> $a A sequence of entries.
     * @param \Iterator<mixed> $b A sequence of entries.
     */
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

    /**
     * @param iterable<mixed,mixed> $a A sequence of entries.
     * @param iterable<mixed,mixed> $b A sequence of entries.
     */
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
     * @param iterable<mixed,mixed> $a A sequence of entries.
     * @param iterable<mixed,mixed> $b A sequence of entries.
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
     * @param iterable<mixed,mixed> $a A sequence of entries.
     * @param iterable<mixed,mixed> $b A sequence of entries.
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
     * @param iterable<mixed,mixed> $a The first sequence of entries.
     * @param iterable<mixed,mixed> $b The second sequence of entries.
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
     * @param iterable<mixed,mixed> $a The first sequence of entries.
     * @param iterable<mixed,mixed> $b The second sequence of entries.
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
     * @param iterable<mixed,mixed> $a A list of values.
     * @param iterable<mixed,mixed> $b A list of values.
     * @param \Closure $valueEquals The values comparison closure.
     * @return bool true if there is an equal relation between the lists, or else false.
     */
    public static function listHasEqualRelation(iterable $a, iterable $b, \Closure $valueEquals): bool
    {
        return self::sequenceHasInclusionRelation($a, $b, self::true(), $valueEquals, self::sequenceSizeEquals(...));
    }

    /**
     * Check if two lists are in an equal relation using one of the php equal operator (== or ===) as values comparison.
     *
     * Two lists are in an equal relation if they have the same values in the same order.
     *
     * @param iterable<mixed,mixed> $a A list of values.
     * @param iterable<mixed,mixed> $b A list of values.
     * @param bool $strictEquals true if the values comparison is ===, or false for ==.
     * @return bool true if the lists are equals, or else false.
     */
    public static function listEquals(iterable $a, iterable $b, bool $strictEquals = false): bool
    {
        return self::listHasEqualRelation($a, $b, self::equals_mayBeStrict($strictEquals));
    }

    /**
     * Check if a list is the begining of another one according to an external values comparison closure.
     *
     * @param iterable<mixed,mixed> $a The first list of values.
     * @param iterable<mixed,mixed> $b The second list of values.
     * @param \Closure $valueEquals The values comparison closure.
     * @param bool $strictPrefix true if the first list must be smaller than the second, or false if both may have the same size.
     * @return bool true if the first list is a prefix of the second one, or else false.
     */
    public static function listHasPrefixRelation(iterable $a, iterable $b, \Closure $valueEquals, bool $strictPrefix = false): bool
    {
        return self::sequenceHasInclusionRelation($a, $b, self::true(), $valueEquals, self::sequenceSizeIsLowerThan_mayBeStrict($strictPrefix));
    }

    /**
     * Check if a list is the begining of another one using one of the php equal operator (== or ===) as values comparison.
     *
     * @param iterable<mixed,mixed> $a The first list of values.
     * @param iterable<mixed,mixed> $b The second list of values.
     * @param bool $strictEquals true if the values comparison is ===, or false for ==.
     * @param bool $strictPrefix true if the first list must be smaller than the second, or false if both may have the same size.
     * @return bool true if the first list is a prefix of the second one, or else false.
     */
    public static function listPrefixEquals(iterable $a, iterable $b, bool $strictEquals = false, bool $strictPrefix = false): bool
    {
        return self::listHasPrefixRelation($a, $b, self::equals_mayBeStrict($strictEquals), $strictPrefix);
    }
    // ========================================================================

    /**
     * Cartesian product between iterables calling a closure to make a result entry.
     *
     *  Note that a cartesian product has no result if an iterable is empty.
     * 
     * @template K
     * @template V
     * 
     * @param \Closure $makeEntry
     *  - $makeEntry(K $k, V $v):R <br>
     *  The closure must return a R value representing a selected iterable entry ($k => $v).
     * @param iterable<K,V> ...$arrays
     *            A sequence of iterable.
     * @return \Iterator<int,array<int, mixed>> An iterator of array of $makeEntry(key, value):
     *  [ $makeEntry(k_1, v_1), ... ,$makeEntry($k_i, $v_i) ]
     *  where ($k_i => $v_i) is an entry from the i^th iterator.
     */
    public static function cartesianProductMakeEntries(\Closure $makeEntry, iterable ...$arrays): \Iterator
    {
        if (empty($arrays))
            return [];

        foreach ($arrays as $a) {
            $it = Iterables::ensureRewindableIterator($a);
            $keys[] = $it;
            $it->rewind();

            if (!$it->valid())
                return [];

            $result[] = [
                $it->key() => $it->current()
            ];
            $it->next();
        }
        yield $result;

        loop:
        $i = \count($arrays);
        while ($i--) {
            $it = $keys[$i];

            if (!$it->valid()) {
                $it->rewind();
                $result[$i] = $makeEntry($it->key(), $it->current());
                $it->next();
            } else {
                $result[$i] = $makeEntry($it->key(), $it->current());
                $it->next();
                yield $result;
                goto loop;
            }
        }
    }
    /**
     * Cartesian product between iterables where a selected entry is returned as an array of two values:
     * [key, value].
     *
     * @template K
     * @template V
     * 
     * @param iterable<K,V> ...$arrays
     *            A sequence of iterable.
     * @return \Iterator<int,array<int,array<int,K|V>>>
     *  An iterator of array of  [key, value] pairs:
     *  [ [k_1, v_1], ... , [$k_i, $v_i] ]
     *  where ($k_i => $v_i) is an entry from the i^th iterator.
     *  Note that a cartesian product has no result if an iterable is empty.
     */
    public static function cartesianProduct(iterable ...$arrays): \Iterator
    {
        /** @var \Iterator<int,array<int,array<int,K|V>>> */
        return self::cartesianProductMakeEntries(fn ($k, $v) => [$k, $v], ...$arrays);
    }

    // ========================================================================
}

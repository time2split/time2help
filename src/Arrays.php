<?php
declare(strict_types = 1);
namespace Time2Split\Help;

final class Arrays
{
    use Classes\NotInstanciable;

    public static function ensureArray($element): array
    {
        if (\is_array($element))
            return $element;

        return [
            $element
        ];
    }

    // ========================================================================
    public static function keyIterator(array|object $array): \Iterator
    {
        foreach ($array as $k => $notUsed)
            yield $k;
        return;
        unset($notUsed);
    }

    public static function reverseIterator(array|object $array): \Iterator
    {
        for (end($array); ($k = key($array)) !== null; prev($array)) {
            yield $k => current($array);
        }
    }

    public static function reverseKeyIterator(array|object $array): \Iterator
    {
        for (end($array); ($k = key($array)) !== null; prev($array)) {
            yield $k;
        }
    }

    // ========================================================================
    public static function first(array $a, $default = null)
    {
        if (empty($a))
            return $default;

        return $a[\array_key_first($a)];
    }

    public static function last(array $a, $default = null)
    {
        if (empty($a))
            return $default;

        return $a[\array_key_last($a)];
    }

    public static function flipKeys(array $a, $default = null)
    {
        $ret = [];

        foreach ($a as $k => $v)
            $ret[$v][] = $k;

        return $ret;
    }

    // ========================================================================
    private static function sequenceSizeIsLowerThan_mayBeStrict(bool $strict): \Closure
    {
        return $strict ? self::sequenceSizeIsStrictlyLowerThan(...) : self::sequenceSizeIsLowerOrEqual(...);
    }

    private static function sequenceSizeIsLowerOrEqual(iterable $a, iterable $b): bool
    {
        return $a->valid();
    }

    private static function sequenceSizeIsStrictlyLowerThan(iterable $a, iterable $b): bool
    {
        return $a->valid() && ! $b->valid();
    }

    private static function sequenceSizeEquals(iterable $a, iterable $b): bool
    {
        return ! $a->valid() && ! $b->valid();
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
    public static function sequenceHasInclusionRelation(iterable $a, iterable $b, \Closure $keyEquals, \Closure $valueEquals, \Closure $endValidation): bool
    {
        $a = Iterators::tryEnsureRewindableIterator($a);
        $b = Iterators::tryEnsureRewindableIterator($b);
        $a->rewind();
        $b->rewind();

        while ($a->valid() && $b->valid()) {

            if (! $keyEquals($a->key(), $b->key()) || ! $valueEquals($a->current(), $b->current()))
                return false;

            $a->next();
            $b->next();
        }
        return $endValidation($a, $b);
    }

    public static function sequenceHasEqualRelation(iterable $a, iterable $b, \Closure $keyEquals, \Closure $valueEquals): bool
    {
        return self::sequenceHasInclusionRelation($a, $b, $keyEquals, $valueEquals, self::sequenceSizeEquals(...));
    }

    public static function sequenceEquals(iterable $a, iterable $b, bool $strictKeyEquals = false, $strictValueEquals = false): bool
    {
        return self::sequenceHasEqualRelation($a, $b, self::equals_mayBeStrict($strictKeyEquals), self::equals_mayBeStrict($strictValueEquals));
    }

    public static function sequenceHasPrefixRelation(iterable $a, iterable $b, \Closure $keyEquals, \Closure $valueEquals, bool $strictPrefix = false): bool
    {
        return self::sequenceHasInclusionRelation($a, $b, $keyEquals, $valueEquals, self::sequenceSizeIsLowerThan_mayBeStrict($strictPrefix));
    }

    public static function sequencePrefixEquals(iterable $a, iterable $b, bool $strictKeyEquals = false, $strictValueEquals = false, bool $strictPrefix = false): bool
    {
        return self::sequenceHasPrefixRelation($a, $b, self::equals_mayBeStrict($strictKeyEquals), self::equals_mayBeStrict($strictValueEquals), $strictPrefix);
    }

    // ========================================================================
    public static function listHasEqualRelation(iterable $a, iterable $b, \Closure $valueEquals): bool
    {
        return self::sequenceHasInclusionRelation($a, $b, self::true(), $valueEquals, self::sequenceSizeEquals(...));
    }

    public static function listEquals(iterable $a, iterable $b, bool $strictEquals = false): bool
    {
        return self::listHasEqualRelation($a, $b, self::equals_mayBeStrict($strictEquals));
    }

    public static function listHasPrefixRelation(iterable $prefix, iterable $list, \Closure $valueEquals, bool $strictPrefix = false): bool
    {
        return self::sequenceHasInclusionRelation($prefix, $list, self::true(), $valueEquals, self::sequenceSizeIsLowerThan_mayBeStrict($strictPrefix));
    }

    public static function listPrefixEquals(iterable $prefix, iterable $list, bool $strictEquals = false, bool $strictPrefix = false): bool
    {
        return self::listHasPrefixRelation($prefix, $list, self::equals_mayBeStrict($strictEquals), $strictPrefix);
    }

    // ========================================================================

    /**
     * Check that an array is in bijection with another array with an equal relation.
     *
     * @param array $a
     *            An array.
     * @param array $b
     *            An array.
     * @param bool $strict
     *            Use the strict comparison as relation (===) or the equals one (==).
     */
    public static function contentEquals(array $a, array $b, bool $strict = false): bool
    {
        if (\count($a) !== \count($b))
            return false;

        return ! self::searchValueWithoutEqualRelation($a, $b, $strict)->valid();
    }

    // ========================================================================
    /**
     * Search for each value of an array that is not associated with another value of a second array with an equal relation.
     *
     * @param array $a
     *            An array.
     * @param array $b
     *            An array.
     * @param bool $strict
     *            Use the strict comparison as relation (===) or the equal one (==).
     * @return \Iterator
     */
    public static function searchValueWithoutEqualRelation(array $a, array $b, bool $strict = false): \Iterator
    {
        return self::usearchValueWithoutRelation(fn ($a, $b) => \array_search($a, $b, $strict), $a, $b);
    }

    /**
     * Search for each value of an array that is associated with another value of a second array with an equals relation.
     *
     * @param array $a
     *            An array.
     * @param array $b
     *            An array.
     * @param bool $strict
     *            Use the strict comparison as relation (===) or the equals one (==).
     * @return \Iterator
     */
    public static function searchEqualValueRelations(array $a, array $b, bool $strict = false): \Iterator
    {
        return self::usearchValueRelations(fn ($a, $b) => \array_search($a, $b, $strict), $a, $b);
    }

    /**
     * Search for each value of an array that cannot be associated with another value of the other array.
     *
     * @param \Closure $searchRelation
     *            The callable of the form searchRelation(mixed $value, array $array):mixed to valid a relation.
     *            The callable must return a key or $array with which $value is in relation or return false if there is no relation.
     * @param array $a
     *            The array to associate from.
     * @param array $b
     *            The array to associate to.
     * @return \Iterator Returns an \Iterator of $k => $v entries from $a without relation with an entry of $b.
     */
    public static function usearchValueWithoutRelation(\Closure $searchRelation, array $a, array $b): \Iterator
    {
        foreach ($a as $k => $v) {
            $krel = $searchRelation($v, $b);

            if (false === $krel)
                yield $k => $v;
            else
                unset($b[$krel]);
        }
    }

    /**
     * Search for each value of an array that is associated with another value of the other array.
     *
     * @param \Closure $searchRelation
     *            The callable of the form searchRelation(mixed $value, array $array):mixed to valid a relation.
     *            The callable must return a key or $array with which $value is in relation or return false if there is no relation.
     * @param array $a
     *            The array to associate from.
     * @param array $b
     *            The array to associate to.
     * @return \Iterator Returns an \Iterator of $ka => $kb entries where $ka => $va is an entry of $a in relation with $kb => $vb an entry of $b.
     */
    public static function usearchValueRelations(\Closure $searchRelation, array $a, array $b): \Iterator
    {
        foreach ($a as $k => $v) {
            $krel = $searchRelation($v, $b);

            if (false !== $krel) {
                yield $k => $krel;
                unset($b[$krel]);
            }
        }
    }

    // ========================================================================
    /**
     * Cartesian product between iterables.
     *
     * @param iterable ...$arrays
     *            A sequence of iterable.
     * @return \Iterator An iterator of array of key => value pairs: [ [k_1 => v_1], ... ,[$k_i => $v_i] ]
     *         where $k_i => $v_i is an entry from the i^th iterator.
     *         Note that a cartesian product has no result if an iterable is empty.
     */
    public static function cartesianProduct(iterable ...$arrays): \Iterator
    {
        if (empty($arrays)) {
            return [];
        }

        foreach ($arrays as $a) {
            $it = Iterators::tryEnsureRewindableIterator($a);
            $keys[] = $it;
            $it->rewind();

            if (! $it->valid())
                return [];

            $result[] = [
                $it->key() => $it->current()
            ];
            $it->next();
        }
        yield $result;

        loop:
        $i = \count($arrays);
        while ($i --) {
            $it = $keys[$i];

            if (! $it->valid()) {
                $it->rewind();
                $result[$i] = [
                    $it->key() => $it->current()
                ];
                $it->next();
            } else {
                $result[$i] = [
                    $it->key() => $it->current()
                ];
                $it->next();
                yield $result;
                goto loop;
            }
        }
    }

    /**
     * Transform each result of a cartesianProduct() iterator into a simple array of all its pair entries.
     *
     * @param \Iterator $cartesianProduct
     *            The iterator of a cartesian product.
     * @return \Iterator An Iterator of flat array which correspond to the merging of all its pairs [$k_i => $v_i].
     */
    public static function mergeCartesianProduct(\Iterator $cartesianProduct): \Iterator
    {
        foreach ($cartesianProduct as $result)
            yield \array_merge(...$result);
    }

    // ========================================================================
    public static function subSelect(array $a, array $keys, $default = null)
    {
        $ret = [];

        foreach ($keys as $k)
            $ret[$k] = $a[$k] ?? $default;

        return $ret;
    }

    public static function &follow(array &$array, array $path, $default = null)
    {
        if (empty($path))
            return $array;

        $p = &$array;

        for (;;) {
            $k = \array_shift($path);

            if (! \array_key_exists($k, $p))
                return $default;

            $p = &$p[$k];

            if (empty($path))
                return $p;
            if (! is_array($p) && ! empty($path))
                return $default;
        }
    }

    /**
     * Replace each int key by its value.
     *
     * @param array $array
     *            The array subject
     * @param mixed $value
     *            The value to associate to each new key=>value item.
     * @return array
     */
    public static function listValueAsKey(array $array, $value = null): array
    {
        $ret = [];

        foreach ($array as $k => $v) {

            if (\is_int($k))
                $ret[$v] = $value;
            else
                $ret[$k] = $v;
        }
        return $ret;
    }

    // ========================================================================
    public static function pathToRecursiveList(array $path, $val)
    {
        $ret = [];
        $pp = &$ret;

        foreach ($path as $p) {
            $pp[$p] = [];
            $pp = &$pp[$p];
        }
        $pp = $val;
        return $ret;
    }

    public static function updateRecursive($args, array &$array, //
    ?callable $onUnexists = null, //
    ?callable $mapKey = null, //
    ?callable $set = null): //
    void
    {
        if (! \is_array($args))
            $array = $args;

        if (null === $mapKey)
            $mapKey = fn ($k) => $k;
        if (null === $onUnexists)
            $onUnexists = function ($array, $key, $v) {
                throw new \Exception("The key '$key' does not exists in the array: " . implode(',', \array_keys($array)));
            };
        if (null === $set)
            $set = function (&$pp, $v) {
                $pp = $v;
            };

        foreach ($args as $k => $v) {
            $k = $mapKey($k);

            if (! \array_key_exists($k, $array))
                $onUnexists($array, $k, $v);

            $pp = &$array[$k];

            if (\is_array($v)) {

                if (! \is_array($pp))
                    $pp = [];

                self::updateRecursive($v, $pp, $onUnexists, $mapKey, $set);
            } else
                $set($pp, $v);
        }
    }

    public static function update(array $args, array &$array, ?\Closure $onUnexists = null, ?\Closure $mapKey = null): void
    {
        if (null === $mapKey)
            $mapKey = fn ($k) => $k;

        foreach ($args as $k => $v) {
            $k = $mapKey($k);

            if (! \array_key_exists($k, $array)) {

                if ($onUnexists === null)
                    throw new \Exception("The key '$k' does not exists in the array: " . implode(',', \array_keys($array)));
                else
                    $onUnexists($array, $k, $v);
            } else
                $array[$k] = $v;
        }
    }

    public static function update_getRemains(array $args, array &$array, ?\Closure $mapKey = null): array
    {
        $remains = [];
        $fstore = function ($array, $k, $v) use (&$remains): void {
            $remains[$k] = $v;
        };
        self::update($args, $array, $fstore, $mapKey);
        return $remains;
    }

    // ========================================================================
    public static function map_merge(\Closure $callback, array $array): array
    {
        return \array_merge(...\array_map($callback, $array));
    }

    public static function map_unique(\Closure $callback, array $array, int $flags = SORT_REGULAR): array
    {
        return \array_unique(\array_map($callback, $array), $flags);
    }

    public static function map_key(?\Closure $callback, array $array): array
    {
        return \array_combine(\array_map($callback, \array_keys($array)), $array);
    }

    public static function kdelete_get(array &$array, $key, $default = null)
    {
        if (! \array_key_exists($key, $array))
            return $default;

        $ret = $array[$key];
        unset($array[$key]);
        return $ret;
    }

    public static function delete(array &$array, ...$vals): bool
    {
        $ret = true;

        foreach ($vals as $val) {
            $k = \array_search($val, $array);

            if (false === $k)
                $ret = false;
            else
                unset($array[$k]);
        }
        return $ret;
    }

    public static function delete_branches(array &$array, array $branches): bool
    {
        $ret = true;

        foreach ($branches as $branch)
            $ret = self::delete_branch($array, $branch) && $ret;

        return $ret;
    }

    public static function delete_branch(array &$array, array $branch): bool
    {
        $def = (object) [];
        $p = \array_pop($branch);
        $a = &self::follow($array, $branch, $def);

        if ($a === $def)
            return false;

        do {
            unset($a[$p]);

            if (\count($a) > 0) {
                break;
            }
            $p = \array_pop($branch);
            $a = &self::follow($array, $branch);
        } while (null !== $p);

        return true;
    }

    public static function partition(array $array, \Closure $filter, int $mode = 0): array
    {
        $a = \array_filter($array, $filter, $mode);
        $b = \array_diff_key($array, $a);
        return [
            $a,
            $b
        ];
    }

    public static function filter_shift(array &$array, ?\Closure $filter = null, int $mode = 0): array
    {
        $drop = [];
        $ret = [];

        if ($mode === 0)
            $fmakeParams = fn ($k, $v) => [
                $v
            ];
        elseif ($mode === ARRAY_FILTER_USE_KEY)
            $fmakeParams = fn ($k, $v) => (array) $k;
        elseif ($mode === ARRAY_FILTER_USE_BOTH)
            $fmakeParams = fn ($k, $v) => [
                $k,
                $v
            ];
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

    public static function walk_branches(array &$data, ?\Closure $walk, ?\Closure $fdown = null): void
    {
        $toProcess = [
            [
                [],
                &$data
            ]
        ];
        if (null === $walk)
            $walk = fn () => true;
        if (null === $fdown)
            $fdown = fn () => true;

        while (! empty($toProcess)) {
            $nextToProcess = [];

            foreach ($toProcess as $tp) {
                $path = $tp[0];
                $array = &$tp[1];

                foreach ($array as $k => &$val) {
                    $path[] = $k;

                    if (\is_array($val) && ! empty($val)) {

                        if ($fdown($path, $val))
                            $nextToProcess[] = [
                                $path,
                                &$val
                            ];
                    } else
                        $walk($path, $val);

                    \array_pop($path);
                }
            }
            $toProcess = $nextToProcess;
        }
    }

    public static function delete_branches_end(array &$array, array $branches, $delVal = null): void
    {
        foreach ($branches as $branch)
            self::delete_branch_end($array, $branch, $delVal);
    }

    public static function delete_branch_end(array &$array, array $branch, $delVal = null): void
    {
        $ref = &self::follow($array, $branch);
        $ref = $delVal;
        unset($ref);
    }

    public static function walk_depth(array &$data, \Closure $walk): void
    {
        $toProcess = [
            &$data
        ];

        while (! empty($toProcess)) {
            $nextToProcess = [];

            foreach ($toProcess as &$item) {
                $walk($item);

                if (\is_array($item))
                    foreach ($item as &$val)
                        $nextToProcess[] = &$val;
            }
            $toProcess = $nextToProcess;
        }
    }

    public static function is_almost_list(array $array): bool
    {
        $notInt = \array_filter(\array_keys($array), fn ($k) => ! \is_int($k));
        return empty($notInt);
    }

    public static function reindex_list(array &$array): void
    {
        if (! self::is_almost_list($array))
            return;

        $array = \array_values($array);
    }

    public static function reindex_lists_recursive(array &$array): void
    {
        self::walk_depth($array, function (&$val) {
            if (\is_array($val))
                self::reindex_list($val);
        });
    }

    public static function depth(array $data): int
    {
        $ret = 0;
        self::walk_branches($data, function ($path) use (&$ret) {
            $ret = \max($ret, \count($path));
        });
        return $ret;
    }

    public static function nb_branches(array $data): int
    {
        $ret = 0;
        self::walk_branches($data, function () use (&$ret) {
            $ret ++;
        });
        return $ret;
    }

    public static function branches(array $data): array
    {
        $ret = [];
        self::walk_branches($data, function ($path) use (&$ret) {
            $ret[] = $path;
        });
        return $ret;
    }

    // ========================================================================
    public static function linearArrayRecursive(array|\ArrayAccess $subject, array $merge, \Closure $linearizePath): array|\ArrayAccess
    {
        self::walk_branches($merge, function ($path, $val) use ($subject, $linearizePath) {
            $subject[$linearizePath($path)] = $val;
        }, function ($path, $val) use ($subject, $linearizePath) {
            if (\is_array_list($val)) {
                $subject[$linearizePath($path)] = $val;
                return false;
            }
            return true;
        });
        return $subject;
    }
}

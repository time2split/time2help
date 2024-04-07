<?php

declare(strict_types=1);

namespace Time2Split\Help;

use Time2Split\Help\Classes\NotInstanciable;

/**
 * Functions on tree-shapped arrays (recursive arrays).
 * 
 * @package time2help\container
 */
final class TreeArrays
{
    use NotInstanciable;

    /**
     * Implementation for the $mustRecurse closure parameters to traverse
     * an array tree.
     * 
     * @param mixed $value A value/node of a tree.
     * @return bool true if value is an array.
     * 
     * @see TreeArrays::update()
     * @see TreeArrays::walkNodes()
     */
    public static function closureParamMustRecurseForArray(mixed $value): bool
    {
        return \is_array($value);
    }

    /**
     * Implementation for the $hasKey closure parameters to traverse
     * an array tree.
     * 
     * @param mixed $key A possible key of $tree.
     * @param mixed $tree A possible tree.
     * @return bool true if $tree is an array and $tree[$key] exists.
     * 
     * @see TreeArrays::setBranch()
     * @see TreeArrays::follow()
     * @see TreeArrays::followNodes()
     */
    public static function closureParamHasKeyForArray(mixed $key, mixed $tree): bool
    {
        return \is_array($tree) && \array_key_exists($key, $tree);
    }

    /**
     * Implementation for the $addNode closure parameters to update
     * an array tree.
     * 
     * Add $tree[$key] = [] to make a new node.
     * 
     * @param mixed $key A key to add to $tree.
     * @param mixed $tree A reference to a (sub)tree.
     * 
     * @see TreeArrays::setBranch()
     */
    public static function closureParamAddNodeForArray(mixed $key, mixed &$tree): void
    {
        if (!\is_array($tree))
            $tree = [];

        $tree[$key] = [];
    }

    /**
     * Implementation for the $setLeaf closure parameters using to update
     * an array tree.
     * 
     * @param mixed $value A value to assign to the leaf.
     * @param mixed &$leaf A reference to the leaf to assign.
     * 
     * @see TreeArrays::setBranch()
     */
    public static function closureParamSetLeafForArray(mixed $value, mixed &$leaf): void
    {
        $leaf = $value;
    }

    // ========================================================================

    /**
     * Updates a tree.
     * 
     * @template V
     * 
     * @param V[] &$tree A reference to a tree to update.
     * @param iterable<V> $args The entries to update.
     * @param \Closure $mustRecurse
     *  Wether a value must be recursively traversed.
     *  - $mustRecurse(V $value):bool
     */
    public static function update(
        array &$tree,
        iterable $args,
        ?callable $mustRecurse = null,
    ): void {
        if (null === $mustRecurse)
            $mustRecurse = self::closureParamMustRecurseForArray(...);

        $existsp = null;
        $noexistsp = null;
        $exists = &$existsp;
        $noexists = &$noexistsp;

        $existsp = function ($k, $v, &$tree) use (&$exists, &$noexists, $mustRecurse): void {

            if ($mustRecurse($v)) {
                $p = &$tree[$k];

                if (!\is_array($p))
                    $p = [];

                Arrays::updateWithClosures($p, $v, $exists, $noexists);
            }
        };
        $noexistsp = function ($k, $v, &$tree) use (&$exists, &$noexists, $mustRecurse): void {

            if ($mustRecurse($v)) {
                $tree[$k] = [];
                $p = &$tree[$k];
                Arrays::updateWithClosures($p, $v, $exists, $noexists);
            } else
                $tree[$k] = $v;
        };
        Arrays::updateWithClosures($tree, $args, $exists, $noexists);
    }

    // ========================================================================

    /**
     * Sets a branch in a tree.
     * 
     * A branch corresponds to a path to follow recursively in an array.
     * The function assigns a value to a branch in the array.
     * 
     * @template K
     * @template V
     * 
     * @param iterable<K,V> &$tree A tree in which set a branch.
     * @param iterable<int,K> $path A path to traverse in the array.
     * @param ?\Closure $hasKey
     * Checks whether $tree[$key] exists.
     * This can only be called once at the first unexistant key encoutered.
     *  - $hasKey(K $key, V $tree):bool
     * @param ?\Closure $addNode
     * Add a new node.
     *  - $addNode(K $key,V &$tree):void
     * @param ?\Closure $setLeaf
     * Assign a value to the leaf.
     *  - $setLeaf(V $value, V &$leaf):void
     * @param V $value The value to assign to the branch.
     */
    public static function setBranch(
        iterable &$tree,
        iterable $path,
        $value = null,
        \Closure $hasKey = null,
        \Closure $addNode = null,
        \Closure $setLeaf = null,
    ): void {
        if (null === $hasKey)
            $hasKey = self::closureParamHasKeyForArray(...);
        if (null === $addNode)
            $addNode = self::closureParamAddNodeForArray(...);
        if (null === $setLeaf)
            $setLeaf = self::closureParamSetLeafForArray(...);

        $p = &$tree;
        $path = new \NoRewindIterator(Iterables::toIterator($path));

        foreach ($path as $k) {
            if (!$hasKey($k, $p)) {
                // End the construction of the branch
                foreach ($path as $k) {
                    $addNode($k, $p);
                    $p = &$p[$k];
                }
            } else
                $p = &$p[$k];
        }
        $setLeaf($value, $p);
    }

    /**
     * Gets a reference to the value of a branch.
     * 
     * @template K
     * @template V
     * @template D
     * 
     * @param iterable<K,V> &$tree A reference to a tree.
     * @param iterable<int,K> $path The path to follow.
     * @param D $default A default value to return if the branch does not exists.
     * @param ?\Closure $hasKey
     *  Whether $key is a traversable key of a (sub)tree.
     *  - $hasKey($key,$tree):bool
     * @return V|D A reference to the $item reached by following $path, or $default if not existant.
     */
    public static function &follow(iterable &$tree, iterable $path, $default = null, \Closure $hasKey = null): mixed
    {
        if (null === $hasKey)
            $hasKey = self::closureParamHasKeyForArray(...);

        $p = &$tree;

        foreach ($path as $k) {
            if (!$hasKey($k, $p))
                return $default;
            $p = &$p[$k];
        }
        return $p;
    }
    /**
     * Follows a path in a tree.
     * 
     * @template K
     * @template V
     * 
     * @param iterable<K,V> &$tree A reference to a tree.
     * @param iterable<int,K> $path The path to follow.
     * @param ?\Closure $hasKey
     *  Whether $key is a traversable key of a (sub)tree.
     *  - $hasKey($key,$tree):bool
     * @return array<int,V> An array of references to the nodes of the branch, including the root and the leaf.
     */
    public static function followNodes(iterable &$tree, iterable $path, \Closure $hasKey = null): array
    {
        if (null === $hasKey)
            $hasKey = self::closureParamHasKeyForArray(...);

        $p = &$tree;
        $ret = [&$p];

        foreach ($path as $k) {
            if (!$hasKey($k, $p))
                return [];
            $p = &$p[$k];
            $ret[] = &$p;
        }
        return $ret;
    }

    /**
     * Transforms a path into a branch with a leaf value.
     * 
     * Given a path [p_1,p_2,...,p_n] the return is [p_1 => [p_2 => [... => [p_n => $leaf]]]].
     * 
     * @param array<int,string|int> $path A path.
     * @param mixed $leaf The value of the last entry.
     * @return mixed[] A recursive list where the last value is $leaf.
     */
    public static function pathToBranch(array $path, $leaf): array
    {
        $ret = [];
        $pp = &$ret;

        foreach ($path as $p) {
            $pp[$p] = [];
            $pp = &$pp[$p];
        }
        $pp = $leaf;
        return $ret;
    }

    // ========================================================================
    // COUNT

    /**
     * Counts the number of leaves.
     * 
     * @template K
     * @template V
     * 
     * @param iterable<K,V> $tree A tree.
     * @param ?\Closure $fdown
     *  Whether the value following a path is a subtree that must be travelled.
     *  - $fdown(array<int,K> $path, V &$maybeSubTree):bool
     * 
     *  If null then 
     *  - $fdown = fn ($path, $val) => \is_iterable($val)
     * @return int The number of leaves.
     */
    public static function countBranches(iterable $tree, \Closure $fdown = null): int
    {
        $nb = 0;
        self::walkBranches($tree, function () use (&$nb): void {
            $nb++;
        }, $fdown);
        return $nb;
    }

    /**
     * Counts the number of nodes.
     * 
     * @template K
     * @template V
     * 
     * @param iterable<K,V> $tree A tree.
     * @param ?\Closure $fdown
     *  Whether the value following a path is a subtree that must be travelled.
     *  - $fdown(array<int,K> $path, V &$maybeSubTree):bool
     * 
     *  If null then 
     *  - $fdown = fn ($path, $val) => \is_iterable($val)
     * @return int The number of nodes.
     */
    public static function countNodes(iterable $tree, \Closure $fdown = null): int
    {
        $nb = 0;
        self::walkNodes($tree, function () use (&$nb): void {
            $nb++;
        }, $fdown);
        return $nb;
    }

    /**
     * Gets the maximal depth of the array.
     * 
     * @template K
     * @template V
     * 
     * @param iterable<K,V> $tree A tree.
     * @param ?\Closure $fdown
     *  Whether the value following a path is a subtree that must be travelled.
     *  - $fdown(array<int,K> $path, V &$maybeSubTree):bool
     * 
     *  If null then 
     *  - $fdown = fn ($path, $val) => \is_iterable($val)
     * @return int The depth of the array.
     */
    public static function getMaxDepth(iterable $tree, \Closure $fdown = null): int
    {
        $nb = 0;
        self::walkBranches($tree, function ($path) use (&$nb): void {
            $nb = \max($nb, \count($path));
        }, $fdown);
        return $nb;
    }

    // ========================================================================
    // WALK

    /**
     * Retrieves all the maximal paths of a tree.
     * 
     * @template K
     * @template V
     * 
     * @param iterable<K,V> $tree A tree.
     * @param ?\Closure $fdown
     *  Whether the value following a path is a subtree that must be travelled.
     *  - $fdown(array<int,K> $path, V &$maybeSubTree):bool
     * 
     *  If null then 
     *  - $fdown = fn ($path, $val) => \is_iterable($val)
     * @return array<int,array<int,K>> An array of paths.
     */
    public static function branches(iterable $tree, \Closure $fdown = null): array
    {
        $ret = [];
        self::walkBranches($tree, function ($path) use (&$ret) {
            $ret[] = $path;
        }, $fdown);
        return $ret;
    }

    /**
     * Walks through all tree branches.
     * 
     * @template K
     * @template V
     * 
     * @param iterable<K,V> &$tree A reference to a tree to walk through.
     * @param ?\Closure $walk
     *  Do something at the leaf of the branch ($path) with its value ($value).
     *  - $walk(array<int,K> $path, V &$value):void
     * @param ?\Closure $fdown
     *  Whether the value following a path is a subtree that must be travelled.
     *  - $fdown(array<int,K> $path, V &$maybeSubTree):bool
     * 
     *  If null then 
     *  - $fdown = fn ($path, $val) => \is_iterable($val)
     */
    public static function walkBranches(
        iterable &$tree,
        ?\Closure $walk = null,
        ?\Closure $fdown = null
    ): void {
        $toProcess = [
            [
                [],
                &$tree
            ]
        ];
        if (null === $walk)
            $walk = fn () => true;
        if (null === $fdown)
            $fdown = fn ($path, $val) => \is_iterable($val);

        while (!empty($toProcess)) {
            $nextToProcess = [];

            foreach ($toProcess as $tp) {
                $path = $tp[0];
                $tree = &$tp[1];

                foreach ($tree as $k => &$val) {
                    $path[] = $k;

                    if ($fdown($path, $val))
                        $nextToProcess[] = [
                            $path,
                            &$val
                        ];
                    else
                        $walk($path, $val);

                    \array_pop($path);
                }
            }
            $toProcess = $nextToProcess;
        }
    }

    /**
     * Walks through all tree nodes.
     * 
     * Note that the root is a node to traverse.
     * 
     * @template K
     * @template V
     * 
     * @param iterable<K,V> &$tree A reference to a tree to walk through.
     * @param \Closure $walk
     * Do something with a node.
     *  - $walk(V &$node):void
     * @param \Closure $mustRecurse
     *  Check wether a value must be recursively traversed.
     *  - $mustRecurse(V $value):bool
     */
    public static function walkNodes(
        iterable &$tree,
        \Closure $walk,
        \Closure $mustRecurse = null
    ): void {
        if (null === $mustRecurse)
            $mustRecurse = self::closureParamMustRecurseForArray(...);
        $toProcess = [&$tree];

        while (!empty($toProcess)) {
            $nextToProcess = [];

            foreach ($toProcess as &$item) {
                $walk($item);

                if ($mustRecurse($item))
                    foreach ($item as &$val)
                        $nextToProcess[] = &$val;
            }
            $toProcess = $nextToProcess;
        }
    }

    // ========================================================================
    // DELETE

    /**
     * Removes multiple branches from an array tree.
     * 
     * @template V
     * 
     * @param V[] &$tree A reference to an array tree.
     * @param iterable<int|string> ...$paths Paths to the branches to remove.
     * @return array<mixed> Array of entries ($k => $v) where
     *  - $k is the key of the path entry ($k => $path) of the $paths iterable
     *  - $v is the return of self::removeBranch($array, $path)
     * 
     * @see TreeArrays::removeBranch()
     */
    public static function removeArrayBranches(array &$tree, iterable ...$paths): array
    {
        $ret = [];
        foreach ($paths as $k => $path)
            $ret[$k] = self::removeBranch($tree, $path);
        return $ret;
    }

    /**
     * Removes a branch from a tree.
     * 
     * @template K
     * @template V
     * 
     * @param iterable<K,V> &$tree A reference to a tree.
     * @param iterable<int,K> $path The path to the branch to remove.
     * @param ?\Closure $fdown
     *  Whether the value following a path is a subtree that must be travelled.
     *  - $fdown(array<int,K> $path, V &$maybeSubTree):bool
     * 
     *  If null then 
     *  - $fdown = fn ($path, $val) => \is_iterable($val)
     * @return array<int,mixed> A triple [$traversed, $removed, $value] where
     *  - $traversed is the path to the node from wich the branch was removed
     *  - $removed is the part of the branch that was removed
     *  - $value is the leaf value of the branch
     */
    public static function removeBranch(iterable &$tree, iterable $path, \Closure $fdown = null): array
    {
        $path = \iterator_to_array($path);
        $index = self::followNodes($tree, $path, $fdown);

        if (empty($index))
            return [];

        $pathLen = \count($path);
        $value = \array_pop($index);
        $c = 0;
        foreach (\array_reverse($index) as &$i) {

            if (\count($i) !== 1) {
                $k = $path[$pathLen - $c - 1];
                unset($i[$k]);
                break;
            }
            $c++;
        }
        $offset = $pathLen - $c - 1;
        $traversed = \array_slice($path, 0, $offset);
        $removed = \array_slice($path, $offset, $pathLen - $offset);
        return [$traversed, $removed, $value];
    }

    /**
     * Removes some leaves from an array tree.
     * 
     * @template V
     * 
     * @param V[] &$tree A reference to an array tree.
     * @param iterable<string|int> ...$paths The paths to the leaves to remove.
     * @return array<V> Array of entries ($k => $v) where
     *  - $k is the key of the path entry ($k => $path) of the $paths iterable
     *  - $v is the return of self::removeLeaf($array, $path)
     */
    public static function removeArrayLeaves(array &$tree, iterable ...$paths): array
    {
        $ret = [];
        foreach ($paths as $k => $path)
            $ret[$k] = self::removeLeaf($tree, $path);
        return $ret;
    }

    /**
     * Removes a branch from a tree.
     * 
     * @template K
     * @template V
     * 
     * @param iterable<K,V> &$tree A reference to a tree.
     * @param iterable<int,K> $path The path to the leaf to remove.
     * @param ?\Closure $fdown
     *  Whether the value following a path is a subtree that must be travelled.
     *  - $fdown(array<int,K> $path, V &$maybeSubTree):bool
     * 
     *  If null then 
     *  - $fdown = fn ($path, $val) => \is_iterable($val)
     * @return V The removed value.
     */
    public static function removeLeaf(iterable &$tree, iterable $path, \Closure $fdown = null): mixed
    {
        $path = \iterator_to_array($path);
        $index = self::followNodes($tree, $path, $fdown);

        if (empty($index))
            return [];

        $len = \count($path);
        $k = $path[$len - 1];
        unset($index[$len - 1][$k]);
        return $index[$len];
    }
}

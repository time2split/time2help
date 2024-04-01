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

    private static function mustRecurse_default(mixed $value): bool
    {
        return \is_array($value);
    }

    private static function hasKey_default(mixed $key, mixed $tree): bool
    {
        return \is_array($tree) && \array_key_exists($key, $tree);
    }

    private static function addNode_default(mixed $key, mixed &$tree): void
    {
        if (!\is_array($tree))
            $tree = [];

        $tree[$key] = [];
    }

    /**
     * Updates a tree.
     * 
     * @param mixed[] &$tree A reference to a tree to update.
     * @param iterable<mixed> $args The entries to update.
     * @param \Closure $mapKey
     *  - $mapKey($key):int|string
     * 
     *  If set then transform each $args entry to ($mapKey($k) => $v).
     * 
     * @param \Closure $mustRecurse
     *  - $mapKey($value):bool
     * 
     * Check wether a value must be recursively traversed.
     */
    public static function update(
        array &$tree,
        iterable $args,
        ?callable $mapKey = null,
        ?callable $mustRecurse = null,
    ): void {
        if (null === $mapKey)
            $mapKey = fn ($k) => $k;
        if (null === $mustRecurse)
            $mustRecurse = self::mustRecurse_default(...);

        $existsp = null;
        $noexistsp = null;
        $exists = &$existsp;
        $noexists = &$noexistsp;

        $existsp = function ($k, $v, &$tree) use (&$exists, &$noexists, $mapKey, $mustRecurse): void {

            if ($mustRecurse($v)) {
                $p = &$tree[$k];

                if (!\is_array($p))
                    $p = [];

                Arrays::updateWithClosures($p, $v, $exists, $noexists, $mapKey);
            }
        };
        $noexistsp = function ($k, $v, &$tree) use (&$exists, &$noexists, $mapKey, $mustRecurse): void {

            if ($mustRecurse($v)) {
                $tree[$k] = [];
                $p = &$tree[$k];
                Arrays::updateWithClosures($p, $v, $exists, $noexists, $mapKey);
            } else
                $tree[$k] = $v;
        };
        Arrays::updateWithClosures($tree, $args, $exists, $noexists, $mapKey);
    }

    // ========================================================================

    /**
     * Sets a branch in a tree.
     * 
     * A branch corresponds to a path to follow recursively in an array.
     * The function assigns a value to a branch in the array.
     * 
     * @param mixed[] &$tree A tree in which set a branch.
     * @param iterable<int,string|int> $path A path to traverse in the array.
     * @param ?\Closure $hasKey
     *  - $hasKey($key,$tree):bool
     * 
     * Checks whether $tree[$key] exists.
     * This can only be called once at the first unexistant key encoutered.
     * 
     * @param ?\Closure $addNode
     *  - $addNode($key,&$tree):void
     * 
     * Add a new node.
     * @param mixed $value The value to assign to the branch.
     */
    public static function setBranch(
        iterable &$tree,
        iterable $path,
        $value = null,
        \Closure $hasKey = null,
        \Closure $addNode = null,
    ): void {
        if (null === $hasKey)
            $hasKey = self::hasKey_default(...);
        if (null === $addNode)
            $addNode = self::addNode_default(...);

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
        $p = $value;
    }

    /**
     * Gets a reference to the leaf of a branch.
     * 
     * @param mixed[] &$tree A reference to a tree.
     * @param iterable<int,string|int> $path The path to follow.
     * @param mixed $default A default value to return if the branch does not exists.
     * @param ?\Closure $hasKey
     *  - $hasKey($key,$tree):bool
     * 
     * Checks whether $tree[$key] exists.
     * 
     * @return mixed A reference to the $item reached by following $path, or $default if not existant.
     */
    public static function &follow(iterable &$tree, iterable $path, $default = null, \Closure $hasKey = null): mixed
    {
        if (null === $hasKey)
            $hasKey = self::hasKey_default(...);

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
     * @param mixed[] &$tree A reference to a tree.
     * @param iterable<int,string|int> $path The path to follow.
     * @param ?\Closure $hasKey
     *  - $hasKey($key,$tree):bool
     * 
     * Checks whether $tree[$key] exists.
     * 
     * @return mixed[] An array of references to the nodes of the branch, including the root and the leaf.
     */
    public static function followNodes(iterable &$tree, iterable $path, \Closure $hasKey = null): array
    {
        if (null === $hasKey)
            $hasKey = self::hasKey_default(...);

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
     * Counts the number of branches.
     * 
     * @param iterable<mixed> $tree A tree.
     * @param ?\Closure $fdown
     *  - $fdown(array $path, array &$subTree):bool
     * 
     * Checks whether the travel must go recursively in the subtree of the branch of path $path.
     * @return int The number of branches.
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
     * @param iterable<mixed> $tree A tree.
     * @param ?\Closure $fdown
     *  - $fdown(array $path, array &$subTree):bool
     * 
     * Checks whether the travel must go recursively in the subtree of the branch of path $path.
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
     * @param iterable<mixed> $tree An array.
     * @param ?\Closure $fdown
     *  - $fdown(array $path, array &$subTree):bool
     * 
     * Checks whether the travel must go recursively in the subtree of the branch of path $path.
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
     * Retrieves all the maximal paths of the array.
     * 
     * @param iterable<mixed> $tree A tree.
     * @param ?\Closure $fdown
     *  - $fdown(array $path, array &$subTree):bool
     * 
     * Checks whether the travel must go recursively in the subtree of the branch of path $path.
     * @return array<int,array<int,int|string>> An array of paths.
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
     * @param iterable<mixed> &$tree A tree to walk through.
     * @param ?\Closure $walk
     *  - $walk(array $path, &$value):void
     * 
     * Do something at the leaf of the branch ($path) with its value ($value).
     * 
     * @param ?\Closure $fdown
     *  - $fdown(array $path, array &$subTree):bool
     * 
     * Checks whether the travel must go recursively in the subtree of the branch of path $path.
     * 
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
     * @param mixed[] &$tree A tree to walk through.
     * @param \Closure $walk
     *  - $walk(&$node):void
     * 
     * Do something with a node.
     * 
     * @param \Closure $mustRecurse
     *  - $mapKey($value):bool
     * 
     * Check wether a value must be recursively traversed.
     */
    public static function walkNodes(
        iterable &$tree,
        \Closure $walk,
        \Closure $mustRecurse = null
    ): void {
        if (null === $mustRecurse)
            $mustRecurse = self::mustRecurse_default(...);
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
     * @param mixed[] &$tree A tree.
     * @param iterable<int,int|string> ...$paths Paths to the branches to remove.
     * @return array<int,mixed> Array of entries ($k => $v) where
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
     * @param mixed[] &$tree A tree.
     * @param iterable<int,int|string> $path The path to the branch to remove.
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
     * @param mixed[] &$tree A tree.
     * @param iterable<int,int|string> ...$paths The paths to the leaves to remove.
     * @return array<int,mixed> Array of entries ($k => $v) where
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
     * @param mixed[] &$tree A tree.
     * @param iterable<int,int|string> $path The path to the leaf to remove.
     * @return mixed The removed value.
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

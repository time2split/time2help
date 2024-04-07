<?php

declare(strict_types=1);

namespace Time2Split\Help;

use Time2Split\Help\Classes\NotInstanciable;

/**
 * Functions on tree-shapped arrays (recursive arrays).
 * 
 * @package time2help\container
 */
final class ArrayTrees
{
    use NotInstanciable;

    /**
     * Implementation for the $isNode closure parameters to traverse
     * an array tree.
     * 
     * @param mixed $value A value/node of a tree.
     * @return bool true if value is an array.
     * 
     * @see TreeArrays::update()
     * @see TreeArrays::walkNodes()
     */
    public static function defaultClosure_isNode(mixed $value): bool
    {
        return \is_array($value);
    }

    // ========================================================================

    /**
     * Updates a tree.
     * 
     * @template V
     * 
     * @param V[] &$tree A reference to a tree to update.
     * @param iterable<V> $args The entries to update.
     * @param ?\Closure $isNode
     *  Whether a value is a node that must be walked through (return true),
     *  or else is a leaf (return false).
     *  - $isNode(V &$node):bool
     */
    public static function update(
        array &$tree,
        iterable $args,
        ?callable $isNode = null,
    ): void {
        if (null === $isNode)
            $isNode = self::defaultClosure_isNode(...);

        $existsp = null;
        $noexistsp = null;
        $exists = &$existsp;
        $noexists = &$noexistsp;

        $existsp = function ($k, $v, &$tree) use (&$exists, &$noexists, $isNode): void {

            if ($isNode($v)) {
                $p = &$tree[$k];

                if (!\is_array($p))
                    $p = [];

                Arrays::updateWithClosures($p, $v, $exists, $noexists);
            }
        };
        $noexistsp = function ($k, $v, &$tree) use (&$exists, &$noexists, $isNode): void {

            if ($isNode($v)) {
                $tree[$k] = [];
                $p = &$tree[$k];
                Arrays::updateWithClosures($p, $v, $exists, $noexists);
            } else
                $tree[$k] = $v;
        };
        Arrays::updateWithClosures($tree, $args, $exists, $noexists);
    }

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
    // public static function removeArrayBranches(array &$tree, iterable ...$paths): array
    // {
    //     $ret = [];
    //     foreach ($paths as $k => $path)
    //         $ret[$k] = self::removeBranch($tree, $path);
    //     return $ret;
    // }

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
    // public static function removeArrayLeaves(array &$tree, iterable ...$paths): array
    // {
    //     $ret = [];
    //     foreach ($paths as $k => $path)
    //         $ret[$k] = self::removeLeaf($tree, $path);
    //     return $ret;
    // }
}

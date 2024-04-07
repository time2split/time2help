<?php

declare(strict_types=1);

namespace Time2Split\Help;

use Time2Split\Help\Classes\NotInstanciable;

/**
 * Functions on tree-shapped iterables (recursive iterables).
 * 
 * @package time2help\container
 */
final class IterableTrees
{
    use NotInstanciable;

    private static function closureVoid(): void
    {
    }

    /**
     * Implementation for the $hasKey closure parameters to traverse
     * an array tree.
     * 
     * @param mixed $node A node.
     * @param mixed $key A possible key of $tree.
     * @return bool true if $tree is an array and $tree[$key] exists.
     * 
     * @see Trees::setBranch()
     * @see Trees::follow()
     * @see Trees::followNodes()
     */
    public static function defaultClosure_hasKey(mixed $node, mixed $key): bool
    {
        return \is_array($node) && \array_key_exists($key, $node);
    }

    /**
     * Default implementation for the $isNode closure parameters to traverse
     * an array tree.
     * 
     * @param mixed $node A node.
     * @param mixed $key A possible key of $tree.
     * @return bool true if $tree is an array.
     * 
     * @see Trees::countLeaves()
     * @see Trees::countNodes()
     * @see Trees::getMaxDepth()
     * @see Trees::branches()
     * @see Trees::walkBranches()
     * @see Trees::walkNodes()
     * @see Trees::removeBranch()
     * @see Trees::removeLastEdge()
     */
    public static function defaultClosure_isNode(mixed $node, array $path): bool
    {
        return \is_array($node);
    }

    /**
     * Default implementation for the $addEdge closure parameters to update
     * an array tree.
     * 
     * Add $tree[$key] = [] to make a new node.
     * 
     * @param mixed &$node A reference to a node.
     * @param mixed $key A key to add to $tree.
     * 
     * @see Trees::setBranch()
     */
    public static function defaultClosure_addEdge(mixed &$node, mixed $key): void
    {
        if (!\is_array($node))
            $node = [];

        $node[$key] = [];
    }

    /**
     * Default implementation for the $dropEdge closure parameters to update
     * an array tree.
     * 
     * Call: unset($node[$key]);
     * 
     * @param mixed &$node A reference to a node.
     * @param mixed $key A key to add to $tree.
     * 
     * @see Trees::removeBranch()
     * @see Trees::removeLastEdge()
     */
    public static function defaultClosure_dropEdge(mixed &$node, mixed $key): void
    {
        unset($node[$key]);
    }

    /**
     * Default implementation for the $setLeaf closure parameters using to update
     * an array tree.
     * 
     * @param mixed &$leaf A reference to the leaf to assign.
     * @param mixed $value A value to assign to the leaf.
     * 
     * @see Trees::setBranch()
     */
    public static function defaultClosure_setLeaf(mixed &$leaf, mixed $value): void
    {
        $leaf = $value;
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
     *  Whether $key is a traversable key of a node.
     *  - $hasKey(V $node, K $key):bool
     * This can only be called once at the first unexistant key encoutered.
     * @param ?\Closure $addEdge
     * Add a sub-node.
     *  - $addEdge(&$node, K $key):void
     * @param ?\Closure $setLeaf
     * Assign a value to the leaf.
     *  - $setLeaf(&$leaf, V $value):void
     * @param V $value The value to assign to the branch.
     */
    public static function setBranch(
        iterable &$tree,
        iterable $path,
        $value = null,
        \Closure $hasKey = null,
        \Closure $addEdge = null,
        \Closure $setLeaf = null,
    ): void {
        if (null === $hasKey)
            $hasKey = self::defaultClosure_hasKey(...);
        if (null === $addEdge)
            $addEdge = self::defaultClosure_addEdge(...);
        if (null === $setLeaf)
            $setLeaf = self::defaultClosure_setLeaf(...);

        $p = &$tree;
        $path = new \NoRewindIterator(Iterables::toIterator($path));

        foreach ($path as $k) {
            if (!$hasKey($p, $k)) {
                // End the construction of the branch
                foreach ($path as $k) {
                    $addEdge($p, $k);
                    $p = &$p[$k];
                }
            } else
                $p = &$p[$k];
        }
        $setLeaf($p, $value);
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
     *  Whether $key is a traversable key of a node.
     *  - $hasKey(V $node, K $key):bool
     * @return V|D A reference to the $item reached by following $path, or $default if not existant.
     */
    public static function &follow(iterable &$tree, iterable $path, $default = null, \Closure $hasKey = null): mixed
    {
        if (null === $hasKey)
            $hasKey = self::defaultClosure_hasKey(...);

        $p = &$tree;

        foreach ($path as $k) {
            if (!$hasKey($p, $k))
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
     *  Whether $key is a traversable key of a node.
     *  - $hasKey(V $node, K $key):bool
     * @return array<int,V> An array of references to the traversed nodes of the branch,
     *  including the root and the leaf.
     */
    public static function followNodes(iterable &$tree, iterable $path, \Closure $hasKey = null): array
    {
        if (null === $hasKey)
            $hasKey = self::defaultClosure_hasKey(...);

        $p = &$tree;
        $ret = [&$p];

        foreach ($path as $k) {
            if (!$hasKey($p, $k))
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
     * @param ?\Closure $isNode
     *  Whether the value following a path is a node that must be traversed (return true),
     *  or else is a leaf (return false).
     *  - $isNode(V &$node, array<int,K> $path):bool
     * @return int The number of leaves.
     */
    public static function countLeaves(iterable $tree, \Closure $isNode = null): int
    {
        $nb = 0;
        self::walkBranches(
            $tree,
            $isNode,
            onLeaf: function () use (&$nb): void {
                $nb++;
            }
        );
        return $nb;
    }

    /**
     * Counts the number of nodes.
     * 
     * @template K
     * @template V
     * 
     * @param iterable<K,V> $tree A tree.
     * @param ?\Closure $isNode
     *  Whether the value following a path is a node that must be traversed (return true),
     *  or else is a leaf (return false).
     *  - $isNode(V &$node, array<int,K> $path):bool
     * @return int The number of nodes.
     */
    public static function countNodes(iterable $tree, \Closure $isNode = null): int
    {
        $nb = 0;
        self::walkNodes(
            $tree,
            $isNode,
            function () use (&$nb): void {
                $nb++;
            },
        );
        return $nb;
    }

    /**
     * Gets the maximal depth of a tree.
     * 
     * @template K
     * @template V
     * 
     * @param iterable<K,V> $tree A tree.
     * @param ?\Closure $isNode
     *  Whether the value following a path is a node that must be traversed (return true),
     *  or else is a leaf (return false).
     *  - $isNode(V &$node, array<int,K> $path):bool
     * @return int The maximal depth of the tree.
     */
    public static function getMaxDepth(iterable $tree, \Closure $isNode = null): int
    {
        $nb = 0;
        self::walkBranches(
            $tree,
            $isNode,
            onLeaf: function ($leaf, $path) use (&$nb): void {
                $nb = \max($nb, \count($path));
            },
        );
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
     * @param ?\Closure $isNode
     *  Whether the value following a path is a node that must be traversed (return true),
     *  or else is a leaf (return false).
     *  - $isNode(V &$node, array<int,K> $path):bool
     * @return array<int,array<int,K>> An array of paths.
     */
    public static function branches(iterable $tree, \Closure $isNode = null): array
    {
        $ret = [];
        self::walkBranches(
            $tree,
            $isNode,
            onLeaf: function ($node, $path) use (&$ret) {
                $ret[] = $path;
            },
        );
        return $ret;
    }

    /**
     * Walks through all branches.
     * 
     * @template K
     * @template V
     * 
     * @param iterable<K,V> &$tree A reference to a tree to walk through.
     * @param ?\Closure $isNode
     *  Whether the value following a path is a node that must be traversed (return true),
     *  or else is a leaf (return false).
     *  - $isNode(V &$node, array<int,K> $path):bool
     * @param ?\Closure $onNode
     *  Do something with the root or an interal node (not a leaf).
     *  - $onNode(V &$node, array<int,K> $path):void
     * @param ?\Closure $onLeaf
     *  Do something with a leaf.
     *  - $onLeaf(V &$leaf, array<int,K> $path):void
     */
    public static function walkBranches(
        iterable &$tree,
        \Closure $isNode = null,
        \Closure $onNode = null,
        \Closure $onLeaf = null,
    ): void {
        if (null === $onNode)
            $onNode = self::closureVoid(...);
        if (null === $onLeaf)
            $onLeaf = self::closureVoid(...);
        if (null === $isNode)
            $isNode = self::defaultClosure_isNode(...);

        $toProcess = [[[], &$tree]];

        while (!empty($toProcess)) {
            $nextToProcess = [];

            foreach ($toProcess as [$path, &$tree]) {
                $onNode($tree, $path);

                foreach ($tree as $k => &$val) {
                    $path[] = $k;

                    if ($isNode($val, $path))
                        $nextToProcess[] = [$path, &$val];
                    else
                        $onLeaf($val, $path);

                    \array_pop($path);
                }
            }
            $toProcess = $nextToProcess;
        }
    }


    /**
     * Walks through all tree nodes (leaves included).
     * 
     * @template K
     * @template V
     * 
     * @param iterable<K,V> &$tree A reference to a tree to walk through.
     * @param ?\Closure $isNode
     *  Whether the value following a path is a node that must be traversed (return true),
     *  or else is a leaf (return false).
     *  - $isNode(V &$node, array<int,K> $path):bool
     * @param ?\Closure $onAnyNode
     *  Do something with a node (root, internals, leaves).
     *  - $onAnyNode(V &$node, array<int,K> $path):void
     */
    public static function walkNodes(
        iterable &$tree,
        \Closure $isNode = null,
        \Closure $onAnyNode = null,
    ): void {
        self::walkBranches($tree, $isNode, $onAnyNode, $onAnyNode);
    }

    // ========================================================================
    // DELETE

    /**
     * Removes a branch from a tree.
     * 
     * @template K
     * @template V
     * 
     * @param iterable<K,V> &$tree A reference to a tree.
     * @param iterable<int,K> $path The path to the branch to remove.
     * @param ?\Closure $isNode
     *  Whether the value following a path is a node that must be traversed (return true),
     *  or else is a leaf (return false).
     *  - $isNode(V &$node, array<int,K> $path):bool
     * @param ?\Closure $dropEdge
     *  Unset an edge from a parent node.
     *  - $dropEdge(V &$node, array<int,K> $path):void
     * @return array<int,mixed> A triple [$traversed, $removed, $value] where
     *  - $traversed is the path to the node from wich the branch was removed
     *  - $removed is the part of the branch that was removed
     *  - $value is the leaf value of the removed branch
     */
    public static function removeBranch(
        iterable &$tree,
        iterable $path,
        \Closure $isNode = null,
        \Closure $dropEdge = null,
    ): array {
        $path = \iterator_to_array($path);
        $index = self::followNodes($tree, $path, $isNode);

        if (empty($index))
            return [];
        if (null === $dropEdge)
            $dropEdge = self::defaultClosure_dropEdge(...);

        $pathLen = \count($path);
        $value = \array_pop($index);
        $c = 0;
        foreach (\array_reverse($index) as &$i) {

            if (\count($i) !== 1) {
                $k = $path[$pathLen - $c - 1];
                $dropEdge($i, $k);
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
     * Removes an edge and it subtree.
     * 
     * @template K
     * @template V
     * 
     * @param iterable<K,V> &$tree A reference to a tree.
     * @param iterable<int,K> $path The path to the leaf to remove.
     * @param ?\Closure $isNode
     *  Whether the value following a path is a node that must be traversed (return true),
     *  or else is a leaf (return false).
     *  - $isNode(V &$node, array<int,K> $path):bool
     * @param ?\Closure $dropEdge
     *  Unset an edge from a parent node.
     *  - $dropEdge(V &$node, array<int,K> $path):void
     * @return V The removed subtree.
     */
    public static function removeLastEdge(
        iterable &$tree,
        iterable $path,
        \Closure $isNode = null,
        \Closure $dropEdge = null,
    ): mixed {
        $path = \iterator_to_array($path);
        $index = self::followNodes($tree, $path, $isNode);

        if (empty($index))
            return [];
        if (null === $dropEdge)
            $dropEdge = self::defaultClosure_dropEdge(...);

        $len = \count($path);
        $k = $path[$len - 1];
        $dropEdge($index[$len - 1], $k);
        return $index[$len];
    }
}

<?php

declare(strict_types=1);

namespace Time2Split\Help;

use Time2Split\Help\Classes\NotInstanciable;

final class TreeArrays
{
    use NotInstanciable;

    /**
     * 
     */
    public static function updateRecursive(
        $args,
        array &$array,
        ?callable $onUnexists = null,
        ?callable $mapKey = null,
        ?callable $set = null,
    ): void {
        if (!\is_array($args))
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

            if (!\array_key_exists($k, $array))
                $onUnexists($array, $k, $v);

            $pp = &$array[$k];

            if (\is_array($v)) {

                if (!\is_array($pp))
                    $pp = [];

                self::updateRecursive($v, $pp, $onUnexists, $mapKey, $set);
            } else
                $set($pp, $v);
        }
    }
    /**
     * Follows a path in an array.
     * 
     * @template V
     * @param V[] &$array A reference to an array.
     * @param (string|int)[] $path The path to follow.
     * @param mixed $default A default value.
     * @return mixed[]|mixed A reference to the $item reached by following $path, or $default if not existant.
     */
    public static function &follow(array &$array, array $path, $default = null)
    {
        if (empty($path))
            return $array;

        $p = &$array;

        for (;;) {
            $k = \array_shift($path);

            if (!\array_key_exists($k, $p))
                return $default;

            $p = &$p[$k];

            if (empty($path))
                return $p;
            if (!is_array($p))
                return $default;
        }
        throw new \AssertionError();
    }

    /**
     * Transform an array representing a path to a recursive list.
     * 
     * Given a path [p_1,p_2,...,p_n] the return is [p_1,[p_2,[...,[p_n => $leaf]]]].
     * 
     * @template V
     * @param V[] $path A path.
     * @param mixed $leaf The value of the last entry.
     * @return mixed[] A recursive list where the last value is $leaf.
     */
    public static function pathToRecursiveList(array $path, $leaf): array
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

        while (!empty($toProcess)) {
            $nextToProcess = [];

            foreach ($toProcess as $tp) {
                $path = $tp[0];
                $array = &$tp[1];

                foreach ($array as $k => &$val) {
                    $path[] = $k;

                    if (\is_array($val) && !empty($val)) {

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

        while (!empty($toProcess)) {
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


    /**
     * Computes the maximal depth of the array.
     * 
     * @param mixed[] $array An array.
     * @return int The depth of the array.
     */
    public static function depth(array $array): int
    {
        $ret = 0;
        self::walk_branches($array, function ($path) use (&$ret) {
            $ret = \max($ret, \count($path));
        });
        return $ret;
    }

    /**
     * Count the number of branches.
     * 
     * @param mixed[] $array An array.
     * @return int The number of branches.
     */
    public static function nb_branches(array $array): int
    {
        $ret = 0;
        self::walk_branches($array, function () use (&$ret) {
            $ret++;
        });
        return $ret;
    }

    /**
     * Retrieves all the maximal paths of the array.
     * 
     * @param mixed[] $array An array.
     * @return array<array<int,string>> An array of paths.
     */
    public static function branches(array $array): array
    {
        $ret = [];
        self::walk_branches($array, function ($path) use (&$ret) {
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

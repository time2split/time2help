<?php
declare(strict_types = 1);
namespace Time2Split\Help;

use Time2Split\Help\Classes\NotInstanciable;

final class Iterators
{
    use NotInstanciable;

    /**
     *
     * @param iterable $array
     *            The iterable to ensure as an \Iterator.
     * @param bool $anIteratorIsRewritable
     *            Consider that an \Iterator (but not \Generator) instance is rewritable.
     * @return \Iterator Return a rewindable iterator.
     * @throws \Exception If cannot iterate through the iterable (eg. Generator already in use).
     */
    public static function tryEnsureRewindableIterator(iterable $array, bool $iteratorClassIsRewindable = true): \Iterator
    {
        if (\is_array($array))
            $ret = new \ArrayIterator($array);
        elseif ($iteratorClassIsRewindable && $array instanceof \Iterator) {

            if ($array instanceof \Generator)
                $ret = new \ArrayIterator(\iterator_to_array($array));
            else
                $ret = $array;
        } else
            $ret = new \ArrayIterator(\iterator_to_array($array));

        return $ret;
    }
}
<?php

namespace Time2Split\Help\Iterable;

/**
 * Flag used for Iterables::parallel().
 * 
 * @package time2help\container
 * @author Olivier Rodriguez (zuri)
 * @see \Time2Split\Help\Iterables::parallelWithFlags()
 */
enum ParallelFlag
{
    /**
     * Do not require all sub iterators to be valid in iteration.
     */
    case NEED_ANY;

    /**
     *  Require all sub iterators to be valid in iteration.
     */
    case NEED_ALL;
}

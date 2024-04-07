<?php

declare(strict_types=1);

namespace Time2Split\Help\Tests\DataProvider;

/**
 * Produces an element in a Provided.
 * 
 * A producer instance is intended to be used as an element of {@see Provided::$data}.
 * It permits to dynamically generate an element when encoutered in a {@see Provided::merge()} iterator. 
 * 
 * @package time2help\tests
 * @author Olivier Rodriguez (zuri)
 */
final class Producer
{

    /**
     * Creates a new producer.
     *
     * @param \Closure $get The closure to produce a new element.
     *  - $get():mixed
     */
    public function __construct(private readonly \Closure $get) //
    {
    }

    /**
     * Produces an element using the closure.
     *
     * @return mixed The produced element.
     */
    public function get(): mixed
    {
        return ($this->get)();
    }
}

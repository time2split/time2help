<?php
declare(strict_types = 1);
namespace Time2Split\Help\Tests\DataProvider;

/**
 * Produce an element in a Provided.
 * 
 * A producer instance is only intended to be used as an element of {@see Provided::$data}.
 * It permits to generate an element when encoutered in {@see Provided::merge()}. 
 * 
 * @package time2help\tests
 * @author Olivier Rodriguez (zuri)
 */
final class Producer
{

    /**
     * Create a new producer.
     *
     * @param \Closure $get The closure to produce a new element.
     */
    public function __construct(private readonly \Closure $get) //
    {}

    /**
     * Produce an element using the closure.
     *
     * @return mixed The produced element.
     */
    public function get(): mixed
    {
        return ($this->get)();
    }
}
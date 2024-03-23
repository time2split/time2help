<?php
declare(strict_types = 1);
namespace Time2Split\Help\Tests\DataProvider;

final class Producer
{

    public function __construct(private readonly \Closure $get) //
    {}

    public function get(): mixed
    {
        return ($this->get)();
    }
}
<?php

declare(strict_types=1);

namespace Time2Split\Help\Tests\DataProvider;

use Time2Split\Help\Iterables;

/**
 * Provides data for unit test.
 * 
 * This class was firstly implemented to simplify the definition of DataProvider methods in PHPUnit.
 * The use case is to define multiple arrays of Provided and proceed to a cartesian product 
 * with the {@see Provided::merge()} function to obtain the datasets for a test method.
 * 
 * @package time2help\tests
 * @author Olivier Rodriguez (zuri)
 * @link https://docs.phpunit.de/en/11.0/writing-tests-for-phpunit.html#data-providers
 */
final class Provided
{

    /**
     * Create a new Provided.
     *
     * @param string $header The header is the name of the dataset in PHPUnit.
     * @param mixed[] $data The arguments to provide to the PHPUnit test method.
     */
    public function __construct(
        public readonly string $header,
        public readonly array $data
    ) {
    }

    /**
     * Cartesian product of arrays of Provided.
     *
     * @param Provided[] ...$provided The Provided arrays to compose.
     * @return iterable<mixed[]> iterable of datasets to send as arguments to a PHPUnit test method.
     * The return of this method is intended to be the return of a PHPUnit DataProvider method. 
     */
    public static function merge(array ...$provided): iterable
    {
        return (function () use ($provided) {
            $prod = Iterables::cartesianProductMerger(...$provided);

            foreach ($prod as $line) {
                $header = \implode('/', \array_map(fn (self $p) => $p->header, $line));
                $data = \array_merge(...\array_map(fn (self $p) => $p->data, $line));

                foreach ($data as $k => $v) {
                    if ($v instanceof Producer)
                        $data[$k] = $v->get();
                }
                yield $header => $data;
            }
        })();
    }
}

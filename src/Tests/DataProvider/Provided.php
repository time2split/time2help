<?php
declare(strict_types = 1);
namespace Time2Split\Help\Tests\DataProvider;

use Time2Split\Help\Arrays;

/**
 * Provide data for unit test.
 * 
 * This class was implemented to simplify the definition of DataProvider methods in PHPUnit.
 * The use case is to define multiple arrays of Provided and proceed to a cartesian product 
 * with the {@link Provided::merge()} function to obtain the datasets for a test method.
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
     * @param array $data The arguments to provide to the PHPUnit test method.
     */
    public function __construct( //
    private readonly string $header, //
    private readonly array $data) //
    {}

    /**
     * Cartesian product of arrays of Provided.
     *
     * @param Provided[] ...$provided The Provided arrays to compose.
     * @return iterable of datasets to send as arguments to a PHPUnit test method.
     * The return of this method is intended to be the return of a PHPUnit DataProvider method. 
     */
    public static function merge(array ...$provided): iterable
    {
        return (function () use ($provided) {
            $prod = Arrays::cartesianProduct(...$provided);
            $prod = Arrays::mergeCartesianProduct($prod);

            foreach ($prod as $line) {
                $header = \implode('/', \array_map(fn ($p) => $p->header, $line));
                $data = \array_merge(...\array_map(fn ($p) => $p->data, $line));

                foreach ($data as $k => $v) {
                    if ($v instanceof Producer)
                        $data[$k] = $v->get();
                }
                yield $header => $data;
            }
        })();
    }
}
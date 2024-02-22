<?php
namespace Time2Split\Help;

use Time2Split\Help\Classes\NotInstanciable;

final class Traversables
{
    use NotInstanciable;

    public static function count(\Traversable $list): int
    {
        $i = 0;
        foreach ($list as $NotUsed)
            $i ++;
        return $i;
        unset($NotUsed);
    }

    public static function firstValue(\Traversable $list, $default = null): mixed
    {
        foreach ($list as $v)
            return $v;

        return $default;
    }

    public static function firstKey(\Traversable $list, $default = null): mixed
    {
        foreach ($list as $k => $NotUsed)
            return $k;

        return $default;
        unset($NotUsed);
    }

    public static function firstKeyValue(\Traversable $list, $default = null): mixed
    {
        foreach ($list as $k => $v)
            return [
                $k,
                $v
            ];
        return $default;
    }

    public static function keys(\Traversable $list): mixed
    {
        foreach ($list as $k => $NotUsed)
            yield $k;

        return;
        unset($NotUsed);
    }

    public static function limit(\Traversable $list, int $offset = 0, ?int $length = null): \Generator
    {
        assert($offset >= 0);
        assert($length >= 0);

        if ($length === 0)
            return;

        $i = 0;

        if ($offset === 0) {

            if (null === $length) {

                foreach ($list as $k => $v)
                    yield $k => $v;
            } else {

                foreach ($list as $k => $v) {
                    yield $k => $v;

                    if (-- $length === 0)
                        return;
                }
            }
        } elseif (null === $length) {

            foreach ($list as $k => $v) {

                if ($i === $offset)
                    yield $k => $v;
                else
                    $i ++;
            }
        } else {

            foreach ($list as $k => $v) {

                if ($i === $offset) {
                    yield $k => $v;

                    if (-- $length === 0)
                        return;
                } else
                    $i ++;
            }
        }
    }
}
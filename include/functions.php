<?php

/**
 * Prints human-readable information about some values on STDERR.
 * 
 * @param mixed ...$values The values to print.
 */
function error_dump(...$values)
{
    foreach ($values as $p)
        fwrite(STDERR, print_r($p, true) . "\n");
}

/**
 * Prints human-readable information about some values on STDERR,
 * then call exit().
 * 
 * @param mixed ...$values The values to print.
 */
function error_dump_exit(...$values)
{
    error_dump(...$values);
    exit();
}

/**
 * Whether a value is a list
 * 
 * @param mixed $value A value to check.
 * 
 * @link https://www.php.net/manual/en/function.array-is-list.php array_is_list()
 */
function is_array_list($value): bool
{
    return \is_array($value) && \array_is_list($value);
}

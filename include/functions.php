<?php

function error_dump(...$params)
{
    foreach ($params as $p)
        fwrite(STDERR, print_r($p, true) . "\n");
}

function error_dump_exit(...$params)
{
    error_dump(...$params);
    exit();
}

function is_array_list($array): bool
{
    return \is_array($array) && \array_is_list($array);
}

function str_format(string $s, array $vars): string
{
    return \str_replace(\array_map(fn ($k) => "%$k", \array_keys($vars)), \array_values($vars), $s);
}

function str_empty(string $s): bool
{
    return strlen($s) === 0;
}

function srange($min, $max): string
{
    if ($min === $max)
        return "$min";

    return "$min,$max";
}

function removePrefix(string $s, string $prefix): string
{
    if (0 === \strpos($s, $prefix))
        return \substr($s, \strlen($prefix));

    return $s;
}

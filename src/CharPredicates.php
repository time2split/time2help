<?php

declare(strict_types=1);

namespace Time2Split\Help;

use Time2Split\Help\Classes\NotInstanciable;

/**
 * Functions to create char predicate closures.
 * 
 * @author Olivier Rodriguez (zuri)
 * @package time2help\IO
 */
final class CharPredicates
{
    use NotInstanciable;

    /**
     * A predicate able to valid an input char if it's a specific one.
     * 
     * @param string $char A character.
     * @throws \DomainException If the input is not a character.
     * @return \Closure A predicate on a character.
     */
    public static function char(string $char): \Closure
    {
        if (1 !== \strlen($char))
            throw new \DomainException("Delimiter must be a unique char, have '$char'");

        return fn(string $c) => $c === $char;
    }

    /**
     * A predicate able to valid an input char if it belongs to some strings.
     * 
     * @param string...$chars Strings of valid characters.
     * @return \Closure A predicate on a character.
     */
    public static function oneOf(string ...$chars): \Closure
    {
        $chars = \implode($chars);

        if (0 < \strlen($chars))
            throw new \DomainException("Delimiter must not be empty");

        return fn(string $c) => false !== \strpos($chars, $c);
    }

    /**
     * A predicate validating any input character.
     */
    public static function any(): \Closure
    {
        return fn(string $c) => true;
    }
    /**
     * A predicate validating nothing.
     */
    public static function none(): \Closure
    {
        return fn(string $c) => false;
    }

    /**
     * Whether a character is a given delimiter.
     *
     * @param string $c
     *            The character to test as a possible delimiter.
     * @param string $delimiters
     *            The string of pairs of <open/close> delimiters.
     * @return string|false The closing delimiter if `$c` is a delimiter or false.
     */
    public static function isDelimitation(string $c, string $delimiters): string|false
    {
        for ($i = 0, $n = \strlen($delimiters); $i < $n; $i += 2) {

            if ($delimiters[$i] === $c)
                return $delimiters[$i + 1];
        }
        return false;
    }
}

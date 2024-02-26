<?php
namespace Time2Split\Help;

final class FIO
{
    use Classes\NotInstanciable;

    public static function streamSkipChars($stream, \Closure $predicate): int
    {
        return self::skipChars( //
        fn () => \fgetc($stream), //
        fn () => \fseek($stream, - 1, SEEK_CUR), //
        $predicate);
    }

    public static function streamGetChars($stream, \Closure $predicate): ?string
    {
        return self::getChars( //
        fn () => \fgetc($stream), //
        fn () => \fseek($stream, - 1, SEEK_CUR), //
        $predicate);
    }

    public static function streamGetCharsUntil($stream, \Closure|string $endDelimiter): ?string
    {
        return self::getCharsUntil( //
        fn () => \fgetc($stream), //
        $endDelimiter);
    }

    public static function streamUngetc($stream): bool
    {
        return \fseek($stream, - 1, SEEK_CUR);
    }

    // ========================================================================
    public static function skipChars(\Closure $fgetc, \Closure $fungetc, \Closure $predicate): int
    {
        $nb = 0;

        while (false !== ($c = $fgetc()) && $predicate($c))
            $nb ++;

        if ($c !== false)
            $fungetc();

        return $nb;
    }

    public static function getChars(\Closure $fgetc, \Closure $fungetc, \Closure $predicate): ?string
    {
        $ret = '';

        while (false !== ($c = $fgetc()) && $predicate($c))
            $ret .= $c;

        if ($c !== false)
            $fungetc();

        return strlen($ret) > 0 ? $ret : null;
    }

    public static function skipSimpleDelimitedText(\Closure $fgetc, string $endDelimiter): void
    {
        getSimpleDelimitedText($fgetc, $endDelimiter);
    }

    public static function getCharsUntil(\Closure $fgetc, \Closure|string $endDelimiter): ?string
    {
        if (\is_string($endDelimiter)) {
            $c = \strlen($endDelimiter);

            if (0 === $c)
                $endDelimiter = fn () => true;
            elseif (1 === $c)
                $endDelimiter = fn ($c) => $c === $endDelimiter;
            else
                $endDelimiter = fn ($c) => false !== \strpos($endDelimiter, $c);
        }
        $ret = '';
        $skip = false;

        while (true) {
            $c = $fgetc();

            if ($c === false)
                return strlen($ret) > 0 ? $ret : null;

            if ($c === '\\')
                $skip = true;
            elseif ($endDelimiter($c) && ! $skip)
                return $ret;
            else
                $ret .= $c;

            if ($skip)
                $skip = false;
        }
    }

    /**
     * Test if a character is a delimiter.
     *
     * @param string $c
     *            The caracter to test as possible delimiter
     * @param string $delimiters
     *            The list of pair of <open/close> delimiters
     * @return string|NULL The closing delimiter if $c is a delimiter or null
     */
    public static function isDelimitation(string $c, string $delimiters): ?string
    {
        for ($i = 0, $n = \strlen($delimiters); $i < $n; $i += 2) {

            if ($delimiters[$i] === $c)
                return $delimiters[$i + 1];
        }
        return null;
    }
}
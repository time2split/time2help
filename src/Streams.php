<?php

declare(strict_types=1);

namespace Time2Split\Help;

use Time2Split\Help\Classes\NotInstanciable;

/**
 * Functions for stream resource.
 * 
 * @author Olivier Rodriguez (zuri)
 * @package time2help\IO
 */
final class Streams
{
    use NotInstanciable;

    /**
     * Gets a stream from a string.
     * @return resource
     */
    public static function stringToStream(string $text = '', string $mode = 'r+')
    {
        $stream = \fopen('php://memory', $mode);

        if (false === $stream)
            throw new \AssertionError();

        \fwrite($stream, $text);
        \rewind($stream);
        return $stream;
    }

    /**
     * Checks if a stream is seekable.
     * 
     * @param resource $stream A resource stream.
     * @param null|array<string,mixed> $meta_data
     *      The meta data of the stream obtained from `\stream_get_meta_data()` if available before the function call.
     *      Unless `\stream_get_meta_data()` is called inside the function.
     * @return bool true if the \fseek() function may be used on the stream.
     */
    public static function isSeekableStream($stream, array $meta_data = null): bool
    {
        $meta_data ??= \stream_get_meta_data($stream);
        /** @var bool */
        return $meta_data['seekable'];
    }

    /**
     * Checks if a stream is readable.
     * 
     * @param resource $stream A resource stream.
     * @param null|array<string,mixed> $meta_data
     *      The meta data of the stream obtained from `\stream_get_meta_data()` if available before the function call.
     *      Unless `\stream_get_meta_data()` is called inside the function.
     * @return bool true if the stream is readable
     * 
     * @link https://www.php.net/manual/en/function.stream-get-meta-data.php stream_get_meta_data()
     */
    public static function isReadableStream($stream, array $meta_data = null): bool
    {
        $meta_data ??= \stream_get_meta_data($stream);
        /** @var string */
        $mode = $meta_data['mode'];

        return \str_starts_with($mode, 'r') || \str_contains($mode, '+');
    }

    /**
     * Ensures that a stream is readable.
     * 
     * @param string|resource $stream A stream or a string.
     * @param bool $rewind true to ensure that the stream is rewinded after the function call.
     * @throws \Exception If cannot make a readable stream.
     * @return resource The stream if it is a readable stream, or self::stringToStream($stream) if it is a string. 
     */
    public static function readableStream($stream, bool $rewind = true)
    {
        if (\is_string($stream))
            return self::stringToStream($stream);

        $meta_data = \stream_get_meta_data($stream);

        if (
            \is_resource($stream)
            && \get_resource_type($stream) === 'stream'
            && self::isReadableStream($stream, $meta_data)
        ) {

            if ($rewind) {

                if (!self::isSeekableStream($stream, $meta_data))
                    throw new \Exception("Is not a seekable stream: " . $meta_data['stream_type']);

                \rewind($stream);
            }
            return $stream;
        }
        throw new \Exception("Cannot make it as a readable stream: " . print_r($stream, true));
    }

    // ========================================================================

    /**
     * Skips some characters from a stream according to a predicate.
     * 
     * @param resource $stream A stream.
     * @param \Closure $predicate
     * - `$predicate(string $char)`
     * Returns true if `$char` must be skipped.
     * 
     * @return int The number of read character.
     */
    public static function streamSkipChars($stream, \Closure $predicate): int
    {
        return self::skipChars(
            fn() => \fgetc($stream),
            fn() => \fseek($stream, -1, SEEK_CUR),
            $predicate,
        );
    }
    /**
     * Skips some characters from a stream until a predicate is true.
     * 
     * @param resource $stream A stream.
     * @param \Closure $predicate
     * - `$predicate(string $char)`
     * Returns true if `$char` must end the skipping.
     * @return int The number of read character.
     */
    public static function streamSkipCharsUntil($stream, \Closure $predicate): int
    {
        return \strlen(self::streamGetCharsUntil($stream, $predicate));
    }

    /**
     * Gets some characters from a stream according to a predicate.
     * 
     * @param resource $stream A stream.
     * @param \Closure $predicate
     * - `$predicate(string $char)`
     * Returns true if `$char` must be read.
     * 
     * @return string A string containing the read characters.
     */
    public static function streamGetChars($stream, \Closure $predicate): string
    {
        return self::getChars(
            fn() => \fgetc($stream),
            fn() => \fseek($stream, -1, SEEK_CUR),
            $predicate,
        );
    }

    /**
     * Gets some characters from a stream until a predicate is true.
     * 
     * @param resource $stream A stream.
     * 
     * @param \Closure $predicate
     * - `$predicate(string $char)`
     * Returns true if `$char` must end the reading.
     * 
     * @return string A string containing the read characters.
     */
    public static function streamGetCharsUntil($stream, \Closure $predicate): string
    {
        return self::getCharsUntil(
            fn() => \fgetc($stream),
            fn() => \fseek($stream, -1, SEEK_CUR),
            $predicate,
        );
    }

    /**
     * Decrements the stream position of some chars.
     * 
     * @param resource $stream A stream to decrement.
     * @param int $nb The number of positions to decrement.
     * @return bool true on success, false on failure.
     * @throws \DomainException if $nb is not positive.
     */
    public static function streamUngetc($stream, int $nb = 1): bool
    {
        if ($nb < 0)
            throw new \DomainException("\$nb must be positive, have $nb");

        return 0 === \fseek($stream, -$nb, SEEK_CUR);
    }

    // ========================================================================

    /**
     * Base routine to implement a procedure skipping some chars according to a predicate.
     *
     * @param \Closure $fgetc
     * - `$fgetc()`
     * Reads a char from a stream.
     * 
     * @param \Closure $fungetc
     * - `$fungetc()`
     * Decrements the stream position.
     * 
     * @param \Closure $predicate
     * - `$predicate(string $char)`
     * Returns true if `$char` must be skipped.
     * 
     * @return integer The number of skipped character.
     */
    public static function skipChars(\Closure $fgetc, \Closure $fungetc, \Closure $predicate): int
    {
        $nb = 0;

        while (false !== ($c = $fgetc()) && $predicate($c))
            $nb++;

        if ($c !== false)
            $fungetc();

        return $nb;
    }

    /**
     * Base routine to implement a procedure reading some chars according to a predicate.
     *
     * @param \Closure $fgetc
     * - `$fgetc()`
     * Reads a char from a stream.
     * 
     * @param \Closure $fungetc
     * - `$fungetc()`
     * Decrements the stream position.
     * 
     * @param \Closure $predicate
     * - `$predicate(string $char)`
     * Returns true if `$char` must be read.
     * 
     * @return string The string of the read characters.
     */
    public static function getChars(\Closure $fgetc, \Closure $fungetc, \Closure $predicate): string
    {
        $ret = '';

        while (false !== ($c = $fgetc()) && $predicate($c))
            $ret .= $c;

        if ($c !== false)
            $fungetc();

        return $ret;
    }

    /**
     * Base routine to implement a procedure skipping some chars until a predicate is true.
     *
     * @param \Closure $fgetc
     * - `$fgetc()`
     * Reads a char from a stream.
     * 
     * @param \Closure $fungetc
     * - `$fungetc()`
     * Decrements the stream position.
     * 
     * @param \Closure $predicate
     * - `$predicate(string $char)`
     * Returns true if `$char` must end the skipping.
     * 
     * @return integer The number of skipped character.
     */
    public static function skipCharsUntil(\Closure $fgetc, \Closure $fungetc, \Closure $predicate): int
    {
        $nb = 0;

        while (true) {
            $c = $fgetc();

            if ($c === false)
                return $nb + 1;
            if ($predicate($c)) {
                $fungetc();
                return $nb;
            }
            $nb++;
        }
    }

    /**
     * Base routine to implement a procedure reading some chars until a predicate is true.
     *
     * @param \Closure $fgetc
     * - `$fgetc()`
     * Reads a char from a stream.
     * 
     * @param \Closure $fungetc
     * - `$fungetc()`
     * Decrements the stream position.
     * 
     * @param \Closure $predicate
     * - `$predicate(string $char)`
     * Returns true if `$char` must be read.
     * 
     * @return string The string of the read characters.
     */
    public static function getCharsUntil(\Closure $fgetc, \Closure $fungetc, \Closure $predicate): string
    {
        $ret = '';

        while (true) {
            $c = $fgetc();

            if ($c === false)
                return $ret;
            if ($predicate($c)) {
                $fungetc();
                return $ret;
            }
            $ret .= $c;
        }
    }
}

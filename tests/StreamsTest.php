<?php
declare(strict_types=1);
namespace Time2Split\Help\Tests;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Time2Split\Help\CharPredicates;
use Time2Split\Help\Streams;
use Time2Split\Help\Tests\DataProvider\Provided;

/**
 * @author Olivier Rodriguez (zuri)
 */
final class StreamsTest extends TestCase
{
    public const text = 'Some text';

    public const len = 9;

    public function testStringToStream(): void
    {
        $stream = Streams::stringToStream(self::text);
        $this->assertSame(self::text, \stream_get_contents($stream));
    }

    public function testReadableStream(): void
    {
        $stream = Streams::readableStream(self::text);
        $this->assertSame(self::text, \stream_get_contents($stream));
        $stream = Streams::readableStream($stream, true);
        $this->assertSame(self::text, \stream_get_contents($stream));
        $stream = Streams::readableStream($stream, false);
        $this->assertSame('', \stream_get_contents($stream));
    }

    public function testIsSeekableStream(): void
    {
        $stream = Streams::stringToStream(self::text);
        $this->assertTrue(Streams::isSeekableStream($stream));
        $stream = \fopen('php://stdin', 'w');
        $this->assertFalse(Streams::isSeekableStream($stream));
    }

    public function testIsReadableStream(): void
    {
        $stream = Streams::stringToStream(self::text);
        $this->assertTrue(Streams::isReadableStream($stream));
        $stream = \fopen('php://stdin', 'w');
        $this->assertFalse(Streams::isReadableStream($stream));
    }

    public static function _testSeek(): iterable
    {
        $predUntil = CharPredicates::char(' ');
        $predRead = \ctype_alpha(...);
        $first = 'Some';
        $secnd = ' text';

        $streams = [
            new Provided('fromString', [
                fn () => Streams::stringToStream(self::text),
            ])
        ];

        $contents = [
            new Provided('skipChars', [
                function ($s) use ($predRead) {
                    $nb = Streams::streamSkipChars($s, $predRead);
                    Assert::assertSame(4, $nb);
                    return \stream_get_contents($s);
                },
                $secnd
            ]),
            new Provided('skipChars:0', [
                function ($s) {
                    $nb = Streams::streamSkipChars($s, CharPredicates::none());
                    Assert::assertSame(0, $nb);
                    return \stream_get_contents($s);
                },
                self::text
            ]),
            new Provided('skipChars:all', [
                function ($s) {
                    $nb = Streams::streamSkipChars($s, CharPredicates::any());
                    Assert::assertSame(StreamsTest::len, $nb);
                    return \stream_get_contents($s);
                },
                ''
            ]),
            new Provided('skipCharsUntils', [
                function ($s) use ($predUntil) {
                    $nb = Streams::streamSkipCharsUntil($s, $predUntil);
                    Assert::assertSame(4, $nb);
                    return \stream_get_contents($s);
                },
                $secnd
            ]),
            new Provided('skipCharsUntils:0', [
                function ($s) {
                    $nb = Streams::streamSkipCharsUntil($s, CharPredicates::none());
                    Assert::assertSame(StreamsTest::len, $nb);
                    return \stream_get_contents($s);
                },
                ''
            ]),
            new Provided('skipCharsUntils:all', [
                function ($s) {
                    $nb = Streams::streamSkipCharsUntil($s, CharPredicates::any());
                    Assert::assertSame(0, $nb);
                    return \stream_get_contents($s);
                },
                self::text
            ]),
            new Provided('getChars', [
                fn ($s) => Streams::streamGetChars($s, $predRead),
                $first
            ]),
            new Provided('getChars:0', [
                fn ($s) => Streams::streamGetChars($s, CharPredicates::none()),
                ''
            ]),
            new Provided('getChars:all', [
                fn ($s) => Streams::streamGetChars($s, CharPredicates::any()),
                self::text
            ]),
            new Provided('getCharsUntil', [
                fn ($s) => Streams::streamGetCharsUntil($s, $predUntil),
                $first
            ]),
            new Provided('getCharsUntil:0', [
                fn ($s) => Streams::streamGetCharsUntil($s, CharPredicates::any()),
                ''
            ]),
            new Provided('getCharsUntil:all', [
                fn ($s) => Streams::streamGetCharsUntil($s, CharPredicates::none()),
                self::text
            ]),
            new Provided('ungetc(2)', [
                function ($s) use ($predUntil) {
                    Streams::streamSkipCharsUntil($s, $predUntil);
                    Streams::streamUngetc($s, 2);
                    return \stream_get_contents($s);
                },
                'me text'
            ]),
        ];
        return Provided::merge($streams, $contents);
    }

    #[DataProvider('_testSeek')]
    public function testSeek(\closure $getStream, \Closure $contents, ?string $expect): void
    {
        $this->assertSame($expect, $contents($getStream()));
    }
}
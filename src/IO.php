<?php
namespace Time2Split\Help;

final class IO
{
    use Classes\NotInstanciable;

    public static function olderThan(string $a, string $b)
    {
        return \filemtime($a) < \filemtime($b);
    }

    public static function rrmdir(string $dir, bool $rmRoot = true): void
    {
        $paths = new \RecursiveIteratorIterator( //
        new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS), //
        \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($paths as $pathInfo) {
            $p = $pathInfo->getPathName();

            if ($pathInfo->isFile() || $pathInfo->isLink())
                \unlink($p);
            else
                \rmdir($p);
        }
        if ($rmRoot)
            \rmdir($dir);
    }

    // ========================================================================
    public static function printPHPFile(string $path, $data, bool $compact = false)
    {
        $s = var_export($data, true);

        if ($compact)
            $s = \preg_replace('#\s#', '', $s);

        return \file_put_contents($path, "<?php return $s;");
    }

    // ========================================================================
    private static $wdStack = [];

    public static function wdPush(string $path): void
    {
        \array_push(self::$wdStack, \getcwd());

        if (! \chdir($path))
            throw new \Exception("Cannot chdir to $path");
    }

    public static function wdPop(): void
    {
        if (empty(self::$wdStack))
            throw new \Exception("WD stack is empty");

        \chdir(\array_pop(self::$wdStack));
    }

    public static function wdOp(string $workingDir, \Closure $exec)
    {
        self::wdPush($workingDir);
        $ret = $exec();
        self::wdPop();
        return $ret;
    }

    // ========================================================================
    public static function scandirNoPoints(string $path, bool $getPath = false): array
    {
        $ret = \array_filter(\scandir($path), fn ($f) => $f[0] !== '.');
        \natcasesort($ret);

        if ($getPath)
            $ret = \array_map(fn ($v) => "$path/$v", $ret);

        return $ret;
    }

    // ========================================================================
    public static function get_ob(\Closure $f): string
    {
        \ob_start();
        $f();
        return \ob_get_clean();
    }

    public static function stringToStream(string $text = "")
    {
        $stream = \fopen('php://memory', 'r+');
        \fwrite($stream, $text);
        \rewind($stream);
        return $stream;
    }

    public static function isSeekableStream($stream, array $meta_data = null): bool
    {
        $meta_data ??= \stream_get_meta_data($stream);

        return $meta_data['seekable'];
    }

    public static function isReadableStream($stream, array $meta_data = null): bool
    {
        $meta_data ??= \stream_get_meta_data($stream);
        $mode = $meta_data['mode'];

        return \str_starts_with($mode, 'r') || \str_contains($mode, '+');
    }

    public static function readableStream($stream, bool $rewind = true)
    {
        if (\is_string($stream))
            return self::stringToStream($stream);

        $meta_data ??= \stream_get_meta_data($stream);

        if (\is_resource($stream) && \get_resource_type($stream) === 'stream' && self::isReadableStream($stream, $meta_data)) {

            if ($rewind) {

                if (! self::isSeekableStream($stream, $meta_data))
                    throw new \Exception("Is not a seekable stream: " . $meta_data['stream_type']);

                \rewind($stream);
            }
            return $stream;
        }
        throw new \Exception("Cannot make as a readable stream: " . print_r($stream, true));
    }

    // ========================================================================
    public static function simpleExec(string $cmd, &$output, &$err, ?string $input = null): int
    {
        $parseDesc = fn ($d) => \in_array($d, [
            STDOUT,
            STDERR
        ]) ? $d : [
            'pipe',
            'w'
        ];
        $descriptors = [
            [
                'pipe',
                'r'
            ],
            $parseDesc($output),
            $parseDesc($err)
        ];
        $pipes = null;
        $proc = \proc_open($cmd, $descriptors, $pipes);

        if (null !== $input)
            \fwrite($pipes[0], $input);

        \fclose($pipes[0]);

        while (($status = \proc_get_status($proc))['running'])
            \usleep(10);

        if (isset($pipes[1]))
            $output = \stream_get_contents($pipes[1]);
        if (isset($pipes[2]))
            $err = \stream_get_contents($pipes[2]);

        return $status['exitcode'];
    }

    public static function get_include_contents(string $filename, array $variables = [], string $uniqueVar = '')
    {
        if (is_file($filename)) {

            if (empty($uniqueVar))
                \extract($variables);
            else
                $$uniqueVar = $variables;

            \ob_start();
            include $filename;
            return \ob_get_clean();
        }
        return false;
    }
}
<?php

declare(strict_types=1);

namespace Time2Split\Help;

use Time2Split\Help\Classes\NotInstanciable;

/**
 * Functions for inputs/outputs.
 *
 * @author Olivier Rodriguez (zuri)
 * @package time2help\IO
 */
final class IO
{
    use NotInstanciable;

    /**
     * Finds whether a file is older than another.
     * 
     * @param string $a First file.
     * @param string $b Second file.
     * @return bool true if `\filemtime($a) < \filemtime($b)`.
     */
    public static function olderThan(string $a, string $b): bool
    {
        return \filemtime($a) < \filemtime($b);
    }

    /**
     * Removes recursively a directory contents.
     * 
     * @param string $dir A directory.
     * @param bool $rmRoot If true then removes also the `$dir` directory.
     */
    public static function rrmdir(string $dir, bool $rmRoot = true): void
    {
        /** @var \Iterator<\SplFileInfo> */
        $paths = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

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

    /**
     * Writes a parsable string representation of a variable into a file.
     * 
     * @param string $file The file to write.
     * @param mixed $data The data to write.
     * @param bool $compact If true then avoid spaces in the contents.
     * @return int|false The number of bytes that were written to the file, or false on failure.
     */
    public static function printPHPFile(string $file, mixed $data, bool $compact = false): int|false
    {
        $s = var_export($data, true);

        if ($compact)
            $s = \preg_replace('#\s#', '', $s);

        return \file_put_contents($file, "<?php return $s;");
    }

    // ========================================================================

    /**
     * @var string[]
     */
    private static array $wdStack = [];

    /**
     * Push the current working directory into an internal stack
     * and chdir to another directory.
     * 
     * @param string $workingDir The new working directory to chdir into.
     */
    public static function wdPush(string $workingDir): void
    {
        \array_push(self::$wdStack, \getcwd());

        if (!\chdir($workingDir))
            throw new \Exception("Cannot chdir to $workingDir");
    }

    /**
     * Pop and chdir the working directory previously pushed with `Time2Split\Help\IO::wdPush()`.
     * 
     * @throws \Exception If the stack is empty.
     */
    public static function wdPop(): void
    {
        if (empty(self::$wdStack))
            throw new \Exception("WD stack is empty");

        \chdir(\array_pop(self::$wdStack));
    }

    /**
     * Isolate an operation to be executed into a specific working directory.
     * 
     * @param string $workingDir The working directory for the operation.
     * @param \Closure $exec An operation to execute.
     * @return mixed The return of the operation.
     */
    public static function wdOp(string $workingDir, \Closure $exec)
    {
        self::wdPush($workingDir);
        $ret = $exec();
        self::wdPop();
        return $ret;
    }

    // ========================================================================

    /**
     * List files and directories directly inside the specified path but not the ones
     * begining by a point char (ie: hidden files in a linux filesystem).
     * 
     * @param string $directory A directory to scan.
     * @param bool $getDirectory If true then the returned paths are prefixed with `"$directory/"`.
     * @return string[] The files and directories from $directory.
     * @throws \Exception If not able to scan.
     */
    public static function scandirNoPoints(string $directory, bool $getDirectory = false): array
    {
        $list =  \scandir($directory);

        if (false === $list)
            throw new \Exception("Unable to scan the directory '$directory");

        $ret = \array_filter($list, fn($f) => $f[0] !== '.');
        \natcasesort($ret);

        if ($getDirectory)
            $ret = \array_map(fn($v) => "$directory/$v", $ret);

        return $ret;
    }

    // ========================================================================

    /**
     * Executes an operation and retrieves its output from stdout/stderr as a string.
     * 
     * @param \Closure $exec An operation to execute.
     * @return string A string containing the operation's output from stdin and stderr, or false on failure.
     * @throws \Exception If not able to retrieves the string.
     */
    public static function get_ob(\Closure $exec): string
    {
        \ob_start();
        $exec();
        $ret = \ob_get_clean();

        if ($ret === false)
            throw new \Exception(\sprintf('Unable to retrieves the string buffer, have %d chars to retrieves', \ob_get_length()));

        return $ret;
    }

    /**
     * Executes a cli command with some input, 
     * stores stdin and stderr into variables 
     * and returns its exit code.
     * 
     * The `$output` and `$err` has two roles in the function.
     * Firstly, they define the stdout and stderr descriptor specification in the same way as in the `\proc_open()` php function (`descriptor_spec` parameter).
     * 
     * Each variable can be:
     * - An array describing the pipe to pass to the process.
     * The first element is the descriptor type and the second element is an option for the given type.
     * Valid types are `"pipe"`
     * (the second element is either `"r"` to pass the read end of the pipe to the process, or `"w"` to pass the write end) and
     * `"file"` (the second element is a filename).
     * Note that anything else than `"w"` is treated like `"r"`.
     * - A stream resource representing a real file descriptor (e.g. opened file, a socket, `STDIN`).
     * 
     * Secondly, at the end of the function `$output` is filled with the content of stdout and `$err` with stderr.
     * 
     * @param string $cmd The command to execute as a process.
     * @param mixed  &$output
     * - (input) As an input argument it corresponds to the stdout `\proc_open()`'s `$descriptor_spec` parameter.
     * - (return) After the return it is filled with the stdout stream contents of the process.
     * @param mixed  &$err
     * - (input) As an input argument it corresponds to the stderr `\proc_open()`'s `descriptor_spec` parameter.
     * - (return) After the return it is filled with the stderr stream contents of the process.
     * @param ?string $input An input to send to stdin for the process.
     * 
     * @return int The exit code of the process.
     * 
     * @throws \Exception If not able to execute the command.
     * 
     * @link https://www.php.net/manual/fr/function.proc-open.php proc_open()
     */
    public static function simpleExec(string $cmd, &$output, &$err, ?string $input = null): int
    {
        $parseDesc = fn($d) => \in_array($d, [
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

        if ($proc === false)
            throw new \Exception('Not able to launch a process');

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

    /**
     * Includes a file and retrieves its output from stdout/stderr as a string.
     * 
     * This function can create variables into the symbol table of the included using a combination of the `$variables`/`$uniqueVar` parameters.
     * 
     * @param string $file A file to include.
     * @param array<string,mixed> $variables Variables to import into the symbol table
     *  before the file inclusion (with `\extract($variables)`).
     * @param string $uniqueVar If not empty then it is a variable name to assign
     *  to the array `$variables` before the inclusion (`$$uniqueVar = $variables`).
     *  In this case only `$$uniqueVar` will appears as a variable in the included file.
     * 
     * @return string|false The contents of stdout/stderr as a string, or false if `$file` is not a file.
     * 
     * @link https://www.php.net/manual/en/function.extract.php extract()
     * @link https://www.php.net/manual/en/function.include.php include()
     */
    public static function get_include_contents(string $file, array $variables = [], string $uniqueVar = ''): string|false
    {
        if (\is_file($file)) {

            if (empty($uniqueVar))
                \extract($variables);
            else
                $$uniqueVar = $variables;

            \ob_start();
            include $file;
            return \ob_get_clean();
        }
        return false;
    }
}

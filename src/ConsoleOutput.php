<?php

declare(strict_types=1);

namespace CommonPHP\Console;

use CommonPHP\Console\Contracts\OutputInterface;
use CommonPHP\Console\Exceptions\ConsoleException;
use CommonPHP\Console\Exceptions\InvalidConsoleArgumentException;

class ConsoleOutput implements OutputInterface
{
    public function __construct(
        private mixed $stdout = null,
        private mixed $stderr = null,
    ) {
        $this->stdout ??= defined('STDOUT') ? STDOUT : fopen('php://output', 'w');
        $this->stderr ??= defined('STDERR') ? STDERR : fopen('php://stderr', 'w');

        $this->ensureStream($this->stdout, 'stdout');
        $this->ensureStream($this->stderr, 'stderr');
    }

    public static function buffered(): self
    {
        return new self(fopen('php://temp', 'w+'), fopen('php://temp', 'w+'));
    }

    public function write(string $message = '', bool $newline = false): void
    {
        $this->writeTo($this->stdout, $message . ($newline ? PHP_EOL : ''));
    }

    public function writeln(string $message = ''): void
    {
        $this->write($message, true);
    }

    public function error(string $message = '', bool $newline = false): void
    {
        $this->writeTo($this->stderr, $message . ($newline ? PHP_EOL : ''));
    }

    public function errorln(string $message = ''): void
    {
        $this->error($message, true);
    }

    public function newLine(int $count = 1): void
    {
        if ($count < 1) {
            return;
        }

        $this->write(str_repeat(PHP_EOL, $count));
    }

    public function fetch(): string
    {
        return $this->read($this->stdout);
    }

    public function fetchError(): string
    {
        return $this->read($this->stderr);
    }

    private function ensureStream(mixed $stream, string $name): void
    {
        if (!is_resource($stream)) {
            throw InvalidConsoleArgumentException::because('Console ' . $name . ' must be a writable stream resource.');
        }
    }

    private function writeTo(mixed $stream, string $message): void
    {
        if (@fwrite($stream, $message) === false) {
            throw new ConsoleException('Unable to write to console output stream.');
        }
    }

    private function read(mixed $stream): string
    {
        if (@rewind($stream) === false) {
            return '';
        }

        $contents = stream_get_contents($stream);

        return $contents === false ? '' : $contents;
    }
}

<?php

declare(strict_types=1);

namespace CommonPHP\Console\Contracts;

interface OutputInterface
{
    public function write(string $message = '', bool $newline = false): void;

    public function writeln(string $message = ''): void;

    public function error(string $message = '', bool $newline = false): void;

    public function errorln(string $message = ''): void;

    public function newLine(int $count = 1): void;
}

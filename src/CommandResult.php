<?php

declare(strict_types=1);

namespace CommonPHP\Console;

use CommonPHP\Console\Enums\ExitCode;
use CommonPHP\Console\Exceptions\InvalidConsoleArgumentException;

class CommandResult
{
    private readonly int $exitCode;

    public function __construct(
        ExitCode|int $exitCode = ExitCode::SUCCESS,
        private readonly string $message = '',
        private readonly string $error = '',
    ) {
        $exitCode = $exitCode instanceof ExitCode ? $exitCode->value : $exitCode;

        if ($exitCode < 0) {
            throw InvalidConsoleArgumentException::because('Console exit code cannot be negative.');
        }

        $this->exitCode = $exitCode;
    }

    public static function success(string $message = ''): self
    {
        return new self(ExitCode::SUCCESS, $message);
    }

    public static function failure(string $error = '', ExitCode|int $exitCode = ExitCode::FAILURE): self
    {
        return new self($exitCode, '', $error);
    }

    public static function from(ExitCode|int|self $result): self
    {
        return $result instanceof self ? $result : new self($result);
    }

    public function exitCode(): int
    {
        return $this->exitCode;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function error(): string
    {
        return $this->error;
    }

    public function isSuccessful(): bool
    {
        return $this->exitCode === ExitCode::SUCCESS->value;
    }
}

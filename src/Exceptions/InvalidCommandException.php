<?php

declare(strict_types=1);

namespace CommonPHP\Console\Exceptions;

class InvalidCommandException extends ConsoleException
{
    public static function because(string $reason): self
    {
        return new self($reason);
    }

    public static function invalidName(string $name): self
    {
        return new self('Invalid console command name "' . $name . '".');
    }
}

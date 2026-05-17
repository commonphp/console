<?php

declare(strict_types=1);

namespace CommonPHP\Console\Exceptions;

class InvalidConsoleArgumentException extends ConsoleException
{
    public static function because(string $reason): self
    {
        return new self($reason);
    }

    public static function unknownOption(string $name): self
    {
        return new self('Unknown console option "' . $name . '".');
    }

    public static function missingOptionValue(string $name): self
    {
        return new self('Console option "' . $name . '" requires a value.');
    }

    public static function missingArgument(string $name): self
    {
        return new self('Console argument "' . $name . '" is required.');
    }

    public static function unexpectedArgument(string $value): self
    {
        return new self('Unexpected console argument "' . $value . '".');
    }
}

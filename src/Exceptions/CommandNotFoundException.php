<?php

declare(strict_types=1);

namespace CommonPHP\Console\Exceptions;

class CommandNotFoundException extends ConsoleException
{
    public static function missing(): self
    {
        return new self('No console command was provided.');
    }

    public static function forName(string $name): self
    {
        return new self('Console command "' . $name . '" was not found.');
    }
}

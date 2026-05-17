<?php

declare(strict_types=1);

namespace CommonPHP\Console\Exceptions;

class CommandAlreadyRegisteredException extends ConsoleException
{
    public static function forName(string $name): self
    {
        return new self('A console command named "' . $name . '" is already registered.');
    }

    public static function forAlias(string $alias, string $command): self
    {
        return new self('The console command alias "' . $alias . '" already points to "' . $command . '".');
    }
}

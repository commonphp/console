<?php

declare(strict_types=1);

namespace CommonPHP\Console\Exceptions;

use Throwable;

class CommandExecutionException extends ConsoleException
{
    public static function forCommand(string $name, Throwable $previous): self
    {
        return new self(
            'Console command "' . $name . '" failed: ' . $previous->getMessage(),
            (int) $previous->getCode(),
            $previous,
        );
    }

}

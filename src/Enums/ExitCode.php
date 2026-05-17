<?php

declare(strict_types=1);

namespace CommonPHP\Console\Enums;

enum ExitCode: int
{
    case SUCCESS = 0;
    case FAILURE = 1;
    case INVALID_ARGUMENT = 2;
    case COMMAND_NOT_FOUND = 127;
    case EXCEPTION = 2147483647;

    public function isSuccessful(): bool
    {
        return $this === self::SUCCESS;
    }
}

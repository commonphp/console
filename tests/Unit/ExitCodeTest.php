<?php

declare(strict_types=1);

namespace CommonPHP\Console\Tests\Unit;

use CommonPHP\Console\Enums\ExitCode;
use PHPUnit\Framework\TestCase;

final class ExitCodeTest extends TestCase
{
    public function testExitCodeValues(): void
    {
        self::assertSame(0, ExitCode::SUCCESS->value);
        self::assertSame(1, ExitCode::FAILURE->value);
        self::assertSame(2, ExitCode::INVALID_ARGUMENT->value);
        self::assertSame(127, ExitCode::COMMAND_NOT_FOUND->value);
        self::assertSame(2147483647, ExitCode::EXCEPTION->value);
    }

    public function testOnlySuccessIsSuccessful(): void
    {
        self::assertTrue(ExitCode::SUCCESS->isSuccessful());
        self::assertFalse(ExitCode::FAILURE->isSuccessful());
        self::assertFalse(ExitCode::INVALID_ARGUMENT->isSuccessful());
    }
}

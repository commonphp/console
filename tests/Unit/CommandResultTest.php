<?php

declare(strict_types=1);

namespace CommonPHP\Console\Tests\Unit;

use CommonPHP\Console\CommandResult;
use CommonPHP\Console\Enums\ExitCode;
use CommonPHP\Console\Exceptions\InvalidConsoleArgumentException;
use PHPUnit\Framework\TestCase;

final class CommandResultTest extends TestCase
{
    public function testItCreatesSuccessfulResults(): void
    {
        $result = CommandResult::success('Completed');

        self::assertSame(0, $result->exitCode());
        self::assertSame('Completed', $result->message());
        self::assertSame('', $result->error());
        self::assertTrue($result->isSuccessful());
    }

    public function testItCreatesFailureResults(): void
    {
        $result = CommandResult::failure('Broken', 9);

        self::assertSame(9, $result->exitCode());
        self::assertSame('', $result->message());
        self::assertSame('Broken', $result->error());
        self::assertFalse($result->isSuccessful());
    }

    public function testItNormalizesEnumsIntegersAndExistingResults(): void
    {
        $existing = CommandResult::success();

        self::assertSame($existing, CommandResult::from($existing));
        self::assertSame(ExitCode::FAILURE->value, CommandResult::from(ExitCode::FAILURE)->exitCode());
        self::assertSame(12, CommandResult::from(12)->exitCode());
    }

    public function testItRejectsNegativeExitCodes(): void
    {
        $this->expectException(InvalidConsoleArgumentException::class);

        new CommandResult(-1);
    }
}

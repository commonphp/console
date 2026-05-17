<?php

declare(strict_types=1);

namespace CommonPHP\Console\Tests\Unit;

use CommonPHP\Console\Exceptions\CommandAlreadyRegisteredException;
use CommonPHP\Console\Exceptions\CommandExecutionException;
use CommonPHP\Console\Exceptions\CommandNotFoundException;
use CommonPHP\Console\Exceptions\ConsoleException;
use CommonPHP\Console\Exceptions\InvalidCommandException;
use CommonPHP\Console\Exceptions\InvalidConsoleArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ExceptionsTest extends TestCase
{
    public function testCommandAlreadyRegisteredFactories(): void
    {
        self::assertInstanceOf(ConsoleException::class, CommandAlreadyRegisteredException::forName('demo'));
        self::assertStringContainsString('demo', CommandAlreadyRegisteredException::forName('demo')->getMessage());
        self::assertStringContainsString('alias', CommandAlreadyRegisteredException::forAlias('d', 'demo')->getMessage());
    }

    public function testCommandNotFoundFactories(): void
    {
        self::assertStringContainsString('No console command', CommandNotFoundException::missing()->getMessage());
        self::assertStringContainsString('demo', CommandNotFoundException::forName('demo')->getMessage());
    }

    public function testInvalidCommandFactories(): void
    {
        self::assertStringContainsString('bad', InvalidCommandException::because('bad')->getMessage());
        self::assertStringContainsString('bad name', InvalidCommandException::invalidName('bad name')->getMessage());
    }

    public function testInvalidConsoleArgumentFactories(): void
    {
        self::assertStringContainsString('bad', InvalidConsoleArgumentException::because('bad')->getMessage());
        self::assertStringContainsString('--missing', InvalidConsoleArgumentException::unknownOption('--missing')->getMessage());
        self::assertStringContainsString('--env', InvalidConsoleArgumentException::missingOptionValue('--env')->getMessage());
        self::assertStringContainsString('target', InvalidConsoleArgumentException::missingArgument('target')->getMessage());
        self::assertStringContainsString('extra', InvalidConsoleArgumentException::unexpectedArgument('extra')->getMessage());
    }

    public function testCommandExecutionExceptionKeepsPreviousException(): void
    {
        $previous = new RuntimeException('boom', 3);
        $exception = CommandExecutionException::forCommand('demo', $previous);

        self::assertSame($previous, $exception->getPrevious());
        self::assertSame(3, $exception->getCode());
        self::assertStringContainsString('demo', $exception->getMessage());
        self::assertStringContainsString('boom', $exception->getMessage());
    }
}

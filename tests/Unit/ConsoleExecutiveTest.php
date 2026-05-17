<?php

declare(strict_types=1);

namespace CommonPHP\Console\Tests\Unit;

use CommonPHP\Console\CommandArgument;
use CommonPHP\Console\CommandDefinition;
use CommonPHP\Console\CommandOption;
use CommonPHP\Console\CommandRegistry;
use CommonPHP\Console\CommandResult;
use CommonPHP\Console\CommandRunner;
use CommonPHP\Console\ConsoleExecutive;
use CommonPHP\Console\ConsoleInput;
use CommonPHP\Console\ConsoleOutput;
use CommonPHP\Console\Enums\ExitCode;
use CommonPHP\Console\Exceptions\ConsoleException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ConsoleExecutiveTest extends TestCase
{
    public function testItListsRegisteredCommandsWhenNoCommandIsProvided(): void
    {
        $registry = (new CommandRegistry())->callable('demo', static fn (): int => 0, 'Demo command');
        $output = ConsoleOutput::buffered();

        $status = (new ConsoleExecutive(
            $registry,
            null,
            ConsoleInput::fromArgv(['cli']),
            $output,
        ))->execute();

        self::assertSame(ExitCode::SUCCESS->value, $status);
        self::assertStringContainsString('Available commands:', $output->fetch());
        self::assertStringContainsString('demo', $output->fetch());
    }

    public function testItReportsWhenNoCommandsAreRegistered(): void
    {
        $output = ConsoleOutput::buffered();

        $status = (new ConsoleExecutive(
            new CommandRegistry(),
            null,
            ConsoleInput::fromArgv(['cli']),
            $output,
        ))->execute();

        self::assertSame(ExitCode::SUCCESS->value, $status);
        self::assertStringContainsString('No console commands registered.', $output->fetch());
    }

    public function testItListsCommandsThroughTheDefaultListCommand(): void
    {
        $registry = (new CommandRegistry())->callable('demo', static fn (): int => 0, 'Demo command');
        $output = ConsoleOutput::buffered();

        $status = (new ConsoleExecutive(
            $registry,
            null,
            ConsoleInput::fromArgv(['cli', 'list']),
            $output,
        ))->execute();

        self::assertSame(ExitCode::SUCCESS->value, $status);
        self::assertStringContainsString('demo', $output->fetch());
    }

    public function testRegisteredListCommandOverridesTheDefaultList(): void
    {
        $registry = (new CommandRegistry())->callable('list', static fn (): CommandResult => CommandResult::success('custom list'));
        $output = ConsoleOutput::buffered();

        $status = (new ConsoleExecutive(
            $registry,
            null,
            ConsoleInput::fromArgv(['cli', 'list']),
            $output,
        ))->execute();

        self::assertSame(ExitCode::SUCCESS->value, $status);
        self::assertSame('custom list' . PHP_EOL, $output->fetch());
    }

    public function testItRendersDefaultHelpCommand(): void
    {
        $definition = (new CommandDefinition('deploy', 'Deploy code'))
            ->argument(CommandArgument::required('target', 'Target environment'))
            ->option(CommandOption::flag('force', 'f', 'Force deploy'));
        $registry = (new CommandRegistry())->callable($definition, static fn (): int => 0);
        $output = ConsoleOutput::buffered();

        $status = (new ConsoleExecutive(
            $registry,
            null,
            ConsoleInput::fromArgv(['cli', 'help', 'deploy']),
            $output,
        ))->execute();

        $contents = $output->fetch();

        self::assertSame(ExitCode::SUCCESS->value, $status);
        self::assertStringContainsString('Usage:', $contents);
        self::assertStringContainsString('Deploy code', $contents);
        self::assertStringContainsString('Target environment', $contents);
        self::assertStringContainsString('-f, --force', $contents);
    }

    public function testDefaultHelpCommandWithoutTargetListsCommands(): void
    {
        $registry = (new CommandRegistry())->callable('demo', static fn (): int => 0, 'Demo command');
        $output = ConsoleOutput::buffered();

        $status = (new ConsoleExecutive(
            $registry,
            null,
            ConsoleInput::fromArgv(['cli', 'help']),
            $output,
        ))->execute();

        self::assertSame(ExitCode::SUCCESS->value, $status);
        self::assertStringContainsString('Available commands:', $output->fetch());
    }

    public function testRegisteredHelpCommandOverridesTheDefaultHelp(): void
    {
        $registry = (new CommandRegistry())->callable('help', static fn (): CommandResult => CommandResult::success('custom help'));
        $output = ConsoleOutput::buffered();

        $status = (new ConsoleExecutive(
            $registry,
            null,
            ConsoleInput::fromArgv(['cli', 'help']),
            $output,
        ))->execute();

        self::assertSame(ExitCode::SUCCESS->value, $status);
        self::assertSame('custom help' . PHP_EOL, $output->fetch());
    }

    public function testItRendersCommandHelpForHelpFlags(): void
    {
        $definition = (new CommandDefinition('deploy', 'Deploy code'))
            ->argument(CommandArgument::required('target', 'Target environment'));
        $registry = (new CommandRegistry())->callable($definition, static fn (): int => 0);
        $output = ConsoleOutput::buffered();

        $status = (new ConsoleExecutive(
            $registry,
            null,
            ConsoleInput::fromArgv(['cli', 'deploy', '--help']),
            $output,
        ))->execute();

        self::assertSame(ExitCode::SUCCESS->value, $status);
        self::assertStringContainsString('Usage:', $output->fetch());
    }

    public function testItRendersResultMessagesAndErrors(): void
    {
        $registry = (new CommandRegistry())->callable(
            'demo',
            static fn (): CommandResult => new CommandResult(ExitCode::FAILURE, 'message', 'error'),
        );
        $output = ConsoleOutput::buffered();

        $status = (new ConsoleExecutive(
            $registry,
            null,
            ConsoleInput::fromArgv(['cli', 'demo']),
            $output,
        ))->execute();

        self::assertSame(ExitCode::FAILURE->value, $status);
        self::assertSame('message' . PHP_EOL, $output->fetch());
        self::assertSame('error' . PHP_EOL, $output->fetchError());
    }

    public function testItReturnsInvalidArgumentStatusForBadInput(): void
    {
        $registry = (new CommandRegistry())->callable(
            (new CommandDefinition('demo'))->argument(CommandArgument::required('name')),
            static fn (): CommandResult => CommandResult::success(),
        );
        $output = ConsoleOutput::buffered();

        $status = (new ConsoleExecutive(
            $registry,
            null,
            ConsoleInput::fromArgv(['cli', 'demo']),
            $output,
        ))->execute();

        self::assertSame(ExitCode::INVALID_ARGUMENT->value, $status);
        self::assertStringContainsString('name', $output->fetchError());
    }

    public function testItReturnsCommandNotFoundStatusForUnknownCommands(): void
    {
        $output = ConsoleOutput::buffered();

        $status = (new ConsoleExecutive(
            new CommandRegistry(),
            null,
            ConsoleInput::fromArgv(['cli', 'missing']),
            $output,
        ))->execute();

        self::assertSame(ExitCode::COMMAND_NOT_FOUND->value, $status);
        self::assertStringContainsString('missing', $output->fetchError());
    }

    public function testItReturnsExceptionStatusForUnexpectedCommandFailures(): void
    {
        $registry = (new CommandRegistry())->callable('explode', static function (): never {
            throw new RuntimeException('boom');
        });
        $output = ConsoleOutput::buffered();

        $status = (new ConsoleExecutive(
            $registry,
            null,
            ConsoleInput::fromArgv(['cli', 'explode']),
            $output,
        ))->execute();

        self::assertSame(ExitCode::EXCEPTION->value, $status);
        self::assertStringContainsString('boom', $output->fetchError());
    }

    public function testItReturnsFailureStatusForConsoleExceptions(): void
    {
        $registry = (new CommandRegistry())->callable('bad', static function (): never {
            throw new ConsoleException('console failed');
        });
        $output = ConsoleOutput::buffered();

        $status = (new ConsoleExecutive(
            $registry,
            null,
            ConsoleInput::fromArgv(['cli', 'bad']),
            $output,
        ))->execute();

        self::assertSame(ExitCode::FAILURE->value, $status);
        self::assertStringContainsString('console failed', $output->fetchError());
    }

    public function testItCanUseRegistryFromRunner(): void
    {
        $registry = (new CommandRegistry())->callable('demo', static fn (): CommandResult => CommandResult::success('ok'));
        $output = ConsoleOutput::buffered();

        $status = (new ConsoleExecutive(
            null,
            new CommandRunner($registry),
            ConsoleInput::fromArgv(['cli', 'demo']),
            $output,
        ))->execute();

        self::assertSame(ExitCode::SUCCESS->value, $status);
        self::assertSame('ok' . PHP_EOL, $output->fetch());
    }
}

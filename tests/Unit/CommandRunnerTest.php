<?php

declare(strict_types=1);

namespace CommonPHP\Console\Tests\Unit;

use CommonPHP\Console\CommandArgument;
use CommonPHP\Console\CommandDefinition;
use CommonPHP\Console\CommandOption;
use CommonPHP\Console\CommandRegistry;
use CommonPHP\Console\CommandResult;
use CommonPHP\Console\CommandRunner;
use CommonPHP\Console\ConsoleInput;
use CommonPHP\Console\ConsoleOutput;
use CommonPHP\Console\Enums\ExitCode;
use CommonPHP\Console\Exceptions\CommandExecutionException;
use CommonPHP\Console\Exceptions\CommandNotFoundException;
use CommonPHP\Console\Exceptions\InvalidConsoleArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class CommandRunnerTest extends TestCase
{
    public function testItRunsCallableCommandsAndReturnsResults(): void
    {
        $registry = (new CommandRegistry())->callable(
            (new CommandDefinition('greet', 'Say hello'))
                ->alias('hi')
                ->argument(CommandArgument::required('name'))
                ->option(CommandOption::flag('loud', 'l')),
            static function ($input, $output): CommandResult {
                $name = $input->argument('name');

                if ($input->flag('loud')) {
                    $name = strtoupper($name);
                }

                $output->writeln('Hello ' . $name);

                return CommandResult::success('done');
            },
        );
        $output = ConsoleOutput::buffered();

        $result = (new CommandRunner($registry))->run(
            ConsoleInput::fromArgv(['cli', 'hi', 'Ada', '-l']),
            $output,
        );

        self::assertTrue($result->isSuccessful());
        self::assertSame('done', $result->message());
        self::assertSame('Hello ADA' . PHP_EOL, $output->fetch());
    }

    public function testItRejectsUnknownCommands(): void
    {
        $this->expectException(CommandNotFoundException::class);

        (new CommandRunner(new CommandRegistry()))->run(ConsoleInput::fromArgv(['cli', 'missing']));
    }

    public function testItRejectsMissingCommandNames(): void
    {
        $this->expectException(CommandNotFoundException::class);

        (new CommandRunner(new CommandRegistry()))->run(ConsoleInput::fromArgv(['cli']));
    }

    public function testItNormalizesIntegerAndEnumResults(): void
    {
        $registry = new CommandRegistry();
        $registry
            ->callable('integer', static fn (): int => 5)
            ->callable('enum', static fn (): ExitCode => ExitCode::FAILURE);

        self::assertSame(5, (new CommandRunner($registry))->run(ConsoleInput::fromArgv(['cli', 'integer']))->exitCode());
        self::assertSame(ExitCode::FAILURE->value, (new CommandRunner($registry))->run(ConsoleInput::fromArgv(['cli', 'enum']))->exitCode());
    }

    public function testItCreatesInputAndOutputWhenTheyAreNotProvided(): void
    {
        $previousArgv = $_SERVER['argv'] ?? null;
        $_SERVER['argv'] = ['cli', 'demo'];

        try {
            $registry = (new CommandRegistry())->callable('demo', static fn (): CommandResult => CommandResult::success());
            $result = (new CommandRunner($registry))->run();
        } finally {
            if ($previousArgv === null) {
                unset($_SERVER['argv']);
            } else {
                $_SERVER['argv'] = $previousArgv;
            }
        }

        self::assertSame(0, $result->exitCode());
    }

    public function testItWrapsUnexpectedCommandFailures(): void
    {
        $registry = (new CommandRegistry())->callable('explode', static function (): never {
            throw new RuntimeException('boom');
        });

        $this->expectException(CommandExecutionException::class);
        $this->expectExceptionMessage('explode');

        (new CommandRunner($registry))->run(ConsoleInput::fromArgv(['cli', 'explode']));
    }

    public function testItLetsConsoleExceptionsBubbleWithoutWrapping(): void
    {
        $registry = (new CommandRegistry())->callable('bad', static function (): never {
            throw InvalidConsoleArgumentException::because('bad input');
        });

        $this->expectException(InvalidConsoleArgumentException::class);

        (new CommandRunner($registry))->run(ConsoleInput::fromArgv(['cli', 'bad']));
    }
}

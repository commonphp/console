<?php

declare(strict_types=1);

namespace CommonPHP\Console\Tests\Unit;

use CommonPHP\Console\CommandArgument;
use CommonPHP\Console\CommandDefinition;
use CommonPHP\Console\CommandOption;
use CommonPHP\Console\ConsoleInput;
use CommonPHP\Console\Exceptions\InvalidConsoleArgumentException;
use PHPUnit\Framework\TestCase;

final class ConsoleInputTest extends TestCase
{
    public function testItBindsArgumentsAndOptionsFromArgv(): void
    {
        $definition = (new CommandDefinition('deploy'))
            ->argument(CommandArgument::required('target'))
            ->argument(CommandArgument::variadic('paths'))
            ->option(CommandOption::flag('force', 'f'))
            ->option(CommandOption::value('env', 'e', required: true))
            ->option(CommandOption::value('tag', 't', repeatable: true));

        $input = ConsoleInput::fromArgv([
            'cli',
            'deploy',
            'prod',
            'app',
            'db',
            '-f',
            '--env=staging',
            '-t',
            'one',
            '--tag',
            'two',
        ])->bind($definition);

        self::assertSame('cli', $input->script());
        self::assertSame('deploy', $input->commandName());
        self::assertSame('prod', $input->argument('target'));
        self::assertSame(['app', 'db'], $input->argument('paths'));
        self::assertTrue($input->flag('force'));
        self::assertSame('staging', $input->option('env'));
        self::assertSame(['one', 'two'], $input->option('tag'));
        self::assertTrue($input->hasOption('force'));
        self::assertFalse($input->hasOption('missing'));
    }

    public function testItUsesDefaultsForOptionalValues(): void
    {
        $definition = (new CommandDefinition('deploy'))
            ->argument(CommandArgument::optional('target', default: 'local'))
            ->option(CommandOption::flag('force'))
            ->option(CommandOption::value('env', default: 'dev'))
            ->option(CommandOption::value('tag', repeatable: true));

        $input = ConsoleInput::fromArgv(['cli', 'deploy'])->bind($definition);

        self::assertSame('local', $input->argument('target'));
        self::assertTrue($input->hasArgument('target'));
        self::assertFalse($input->flag('force'));
        self::assertSame('dev', $input->option('env'));
        self::assertSame([], $input->option('tag'));
        self::assertFalse($input->hasOption('env'));
    }

    public function testItParsesNegatedFlags(): void
    {
        $definition = (new CommandDefinition('deploy'))
            ->option(CommandOption::flag('ansi', default: true));

        $input = ConsoleInput::fromArgv(['cli', 'deploy', '--no-ansi'])->bind($definition);

        self::assertFalse($input->flag('ansi'));
        self::assertTrue($input->hasOption('ansi'));
    }

    public function testItParsesStackedShortFlagsAndAttachedValues(): void
    {
        $definition = (new CommandDefinition('deploy'))
            ->option(CommandOption::flag('verbose', 'v'))
            ->option(CommandOption::flag('force', 'f'))
            ->option(CommandOption::value('env', 'e'));

        $input = ConsoleInput::fromArgv(['cli', 'deploy', '-vfeprod'])->bind($definition);

        self::assertTrue($input->flag('verbose'));
        self::assertTrue($input->flag('force'));
        self::assertSame('prod', $input->option('env'));
    }

    public function testItParsesShortValueWithEquals(): void
    {
        $definition = (new CommandDefinition('deploy'))
            ->option(CommandOption::value('env', 'e'));

        $input = ConsoleInput::fromArgv(['cli', 'deploy', '-e=prod'])->bind($definition);

        self::assertSame('prod', $input->option('env'));
    }

    public function testItCanBeConstructedDirectlyWithBoundValues(): void
    {
        $input = new ConsoleInput(
            'cli',
            'demo',
            ['--raw'],
            ['name' => null],
            ['force' => '1', 'tags' => ['a']],
            ['force' => true],
        );

        self::assertSame(['--raw'], $input->rawArguments());
        self::assertSame(['name' => null], $input->arguments());
        self::assertFalse($input->hasArgument('name'));
        self::assertSame('fallback', $input->argument('name', 'fallback'));
        self::assertSame(['force' => '1', 'tags' => ['a']], $input->options());
        self::assertTrue($input->flag('force'));
        self::assertTrue($input->flag('tags'));
        self::assertTrue($input->hasOption('force'));
        self::assertSame('default', $input->option('missing', 'default'));
    }

    public function testItCanReadArgvFromServerGlobals(): void
    {
        $previousArgv = $_SERVER['argv'] ?? null;
        $_SERVER['argv'] = ['cli', 'demo', 'value'];

        try {
            $input = ConsoleInput::fromArgv();
        } finally {
            if ($previousArgv === null) {
                unset($_SERVER['argv']);
            } else {
                $_SERVER['argv'] = $previousArgv;
            }
        }

        self::assertSame('cli', $input->script());
        self::assertSame('demo', $input->commandName());
        self::assertSame(['value'], $input->rawArguments());
    }

    public function testItLeavesCommandNameEmptyWhenFirstTokenIsAnOption(): void
    {
        $input = ConsoleInput::fromArgv(['cli', '--version']);

        self::assertNull($input->commandName());
        self::assertSame('fallback', $input->commandName('fallback'));
        self::assertSame(['--version'], $input->rawArguments());
    }

    public function testBindingTheSameDefinitionTwiceReturnsTheSameInput(): void
    {
        $definition = new CommandDefinition('demo');
        $input = ConsoleInput::fromArgv(['cli', 'demo']);
        $bound = $input->bind($definition);

        self::assertSame($bound, $bound->bind($definition));
    }

    public function testItDetectsHelpRequests(): void
    {
        self::assertTrue(ConsoleInput::fromArgv(['cli', 'demo', '--help'])->wantsHelp());
        self::assertTrue(ConsoleInput::fromArgv(['cli', 'demo', '-h'])->wantsHelp());
        self::assertFalse(ConsoleInput::fromArgv(['cli', 'demo'])->wantsHelp());
    }

    public function testItRejectsMissingRequiredArguments(): void
    {
        $definition = (new CommandDefinition('deploy'))
            ->argument(CommandArgument::required('target'));

        $this->expectException(InvalidConsoleArgumentException::class);
        $this->expectExceptionMessage('target');

        ConsoleInput::fromArgv(['cli', 'deploy'])->bind($definition);
    }

    public function testItRejectsMissingRequiredOptions(): void
    {
        $definition = (new CommandDefinition('deploy'))
            ->option(CommandOption::value('env', required: true));

        $this->expectException(InvalidConsoleArgumentException::class);
        $this->expectExceptionMessage('--env');

        ConsoleInput::fromArgv(['cli', 'deploy'])->bind($definition);
    }

    public function testItRejectsUnknownLongOptions(): void
    {
        $this->expectException(InvalidConsoleArgumentException::class);
        $this->expectExceptionMessage('--missing');

        ConsoleInput::fromArgv(['cli', 'deploy', '--missing'])->bind(new CommandDefinition('deploy'));
    }

    public function testItRejectsUnknownShortOptions(): void
    {
        $this->expectException(InvalidConsoleArgumentException::class);
        $this->expectExceptionMessage('-x');

        ConsoleInput::fromArgv(['cli', 'deploy', '-x'])->bind(new CommandDefinition('deploy'));
    }

    public function testItRejectsMissingLongOptionValues(): void
    {
        $definition = (new CommandDefinition('deploy'))->option(CommandOption::value('env'));

        $this->expectException(InvalidConsoleArgumentException::class);
        $this->expectExceptionMessage('--env');

        ConsoleInput::fromArgv(['cli', 'deploy', '--env'])->bind($definition);
    }

    public function testItRejectsMissingShortOptionValues(): void
    {
        $definition = (new CommandDefinition('deploy'))->option(CommandOption::value('env', 'e'));

        $this->expectException(InvalidConsoleArgumentException::class);
        $this->expectExceptionMessage('-e');

        ConsoleInput::fromArgv(['cli', 'deploy', '-e'])->bind($definition);
    }

    public function testItRejectsValuesForFlags(): void
    {
        $definition = (new CommandDefinition('deploy'))->option(CommandOption::flag('force'));

        $this->expectException(InvalidConsoleArgumentException::class);
        $this->expectExceptionMessage('does not accept a value');

        ConsoleInput::fromArgv(['cli', 'deploy', '--force=yes'])->bind($definition);
    }

    public function testItRejectsUnexpectedArguments(): void
    {
        $this->expectException(InvalidConsoleArgumentException::class);
        $this->expectExceptionMessage('extra');

        ConsoleInput::fromArgv(['cli', 'deploy', 'extra'])->bind(new CommandDefinition('deploy'));
    }

    public function testDoubleDashStopsOptionParsing(): void
    {
        $definition = (new CommandDefinition('echo'))
            ->argument(CommandArgument::variadic('values'));

        $input = ConsoleInput::fromArgv(['cli', 'echo', '--', '--literal', '-x'])->bind($definition);

        self::assertSame(['--literal', '-x'], $input->argument('values'));
    }
}

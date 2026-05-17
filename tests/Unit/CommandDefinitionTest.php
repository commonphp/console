<?php

declare(strict_types=1);

namespace CommonPHP\Console\Tests\Unit;

use CommonPHP\Console\CommandArgument;
use CommonPHP\Console\CommandDefinition;
use CommonPHP\Console\CommandOption;
use CommonPHP\Console\Exceptions\InvalidCommandException;
use PHPUnit\Framework\TestCase;

final class CommandDefinitionTest extends TestCase
{
    public function testItBuildsCommandMetadataFluently(): void
    {
        $definition = CommandDefinition::make('cache:clear', 'Clear cache')
            ->describe('Clear application cache')
            ->alias('cache-clear')
            ->alias('cache-clear')
            ->argument(CommandArgument::required('store', 'Store name'))
            ->option(CommandOption::flag('force', 'f', 'Force clear'));

        self::assertSame('cache:clear', $definition->name());
        self::assertSame('Clear application cache', $definition->description());
        self::assertSame(['cache-clear'], $definition->aliases());
        self::assertSame(['store'], array_keys($definition->arguments()));
        self::assertSame(['force'], array_keys($definition->options()));
        self::assertSame($definition->getOption('force'), $definition->getOptionByShortcut('f'));
        self::assertSame('cli cache:clear [--force] <store>', $definition->usage('cli'));
    }

    public function testItCreatesArgumentsAndOptionsFromStrings(): void
    {
        $definition = (new CommandDefinition('deploy'))
            ->argument('target', 'Target environment', true)
            ->option('env', 'e', 'Environment', true, true, 'prod');

        self::assertSame('target', $definition->getArgument('target')?->name());
        self::assertSame('env', $definition->getOption('env')?->name());
        self::assertSame('--env=VALUE', $definition->getOption('env')?->synopsis());
    }

    public function testItRejectsInvalidCommandNames(): void
    {
        $this->expectException(InvalidCommandException::class);

        new CommandDefinition('bad command');
    }

    public function testItRejectsInvalidAliases(): void
    {
        $this->expectException(InvalidCommandException::class);

        (new CommandDefinition('demo'))->alias('bad alias');
    }

    public function testItRejectsDuplicateArguments(): void
    {
        $this->expectException(InvalidCommandException::class);

        (new CommandDefinition('demo'))
            ->argument('name')
            ->argument('name');
    }

    public function testRequiredArgumentsCannotFollowOptionalArguments(): void
    {
        $this->expectException(InvalidCommandException::class);

        (new CommandDefinition('demo'))
            ->argument(CommandArgument::optional('maybe'))
            ->argument(CommandArgument::required('required'));
    }

    public function testArgumentsCannotFollowVariadicArguments(): void
    {
        $this->expectException(InvalidCommandException::class);

        (new CommandDefinition('demo'))
            ->argument(CommandArgument::variadic('values'))
            ->argument(CommandArgument::optional('after'));
    }

    public function testItRejectsDuplicateOptions(): void
    {
        $this->expectException(InvalidCommandException::class);

        (new CommandDefinition('demo'))
            ->option(CommandOption::flag('verbose'))
            ->option(CommandOption::value('verbose'));
    }

    public function testItRejectsDuplicateOptionShortcuts(): void
    {
        $this->expectException(InvalidCommandException::class);

        (new CommandDefinition('demo'))
            ->option(CommandOption::flag('verbose', 'v'))
            ->option(CommandOption::value('version', 'v'));
    }
}

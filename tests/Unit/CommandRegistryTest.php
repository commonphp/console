<?php

declare(strict_types=1);

namespace CommonPHP\Console\Tests\Unit;

use CommonPHP\Console\CommandDefinition;
use CommonPHP\Console\CommandRegistry;
use CommonPHP\Console\Contracts\AbstractCommand;
use CommonPHP\Console\Contracts\InputInterface;
use CommonPHP\Console\Contracts\OutputInterface;
use CommonPHP\Console\Exceptions\CommandAlreadyRegisteredException;
use CommonPHP\Console\Exceptions\CommandNotFoundException;
use PHPUnit\Framework\TestCase;

final class CommandRegistryTest extends TestCase
{
    public function testItRegistersCommandsAndAliases(): void
    {
        $command = new RegistryFixtureCommand('demo', ['d']);
        $registry = (new CommandRegistry())->register($command);

        self::assertTrue($registry->has('demo'));
        self::assertTrue($registry->has('d'));
        self::assertSame($command, $registry->get('demo'));
        self::assertSame($command, $registry->get('d'));
        self::assertSame(['demo'], $registry->names());
        self::assertSame(['demo' => $command], $registry->all());
        self::assertSame(['demo' => $command], iterator_to_array($registry));
        self::assertCount(1, $registry);
        self::assertFalse($registry->isEmpty());
    }

    public function testItRemovesCommandsAndAliases(): void
    {
        $registry = (new CommandRegistry())->register(new RegistryFixtureCommand('demo', ['d']));

        $registry->remove('d');

        self::assertFalse($registry->has('demo'));
        self::assertFalse($registry->has('d'));
        self::assertTrue($registry->isEmpty());
    }

    public function testItRegistersCallableCommands(): void
    {
        $registry = (new CommandRegistry())->callable('demo', static fn (): int => 0, 'Demo');

        self::assertTrue($registry->has('demo'));
        self::assertSame('Demo', $registry->get('demo')->definition()->description());
    }

    public function testItRejectsDuplicateCommandNames(): void
    {
        $registry = (new CommandRegistry())->register(new RegistryFixtureCommand('demo'));

        $this->expectException(CommandAlreadyRegisteredException::class);

        $registry->register(new RegistryFixtureCommand('demo'));
    }

    public function testItRejectsAliasesThatMatchCommandNames(): void
    {
        $registry = (new CommandRegistry())->register(new RegistryFixtureCommand('demo'));

        $this->expectException(CommandAlreadyRegisteredException::class);

        $registry->register(new RegistryFixtureCommand('other', ['demo']));
    }

    public function testItRejectsDuplicateAliases(): void
    {
        $registry = (new CommandRegistry())->register(new RegistryFixtureCommand('demo', ['d']));

        $this->expectException(CommandAlreadyRegisteredException::class);

        $registry->register(new RegistryFixtureCommand('other', ['d']));
    }

    public function testItRejectsMissingCommands(): void
    {
        $this->expectException(CommandNotFoundException::class);

        (new CommandRegistry())->get('missing');
    }
}

final class RegistryFixtureCommand extends AbstractCommand
{
    /**
     * @param list<string> $aliases
     */
    public function __construct(
        private readonly string $commandName,
        private readonly array $aliases = [],
    ) {
    }

    protected function name(): string
    {
        return $this->commandName;
    }

    protected function configure(CommandDefinition $definition): CommandDefinition
    {
        foreach ($this->aliases as $alias) {
            $definition->alias($alias);
        }

        return $definition;
    }

    public function handle(InputInterface $input, OutputInterface $output): int
    {
        return 0;
    }
}

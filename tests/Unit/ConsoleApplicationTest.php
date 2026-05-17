<?php

declare(strict_types=1);

namespace CommonPHP\Console\Tests\Unit;

use CommonPHP\Console\CommandDefinition;
use CommonPHP\Console\CommandRegistry;
use CommonPHP\Console\CommandRunner;
use CommonPHP\Console\ConsoleApplication;
use CommonPHP\Console\Contracts\AbstractCommand;
use CommonPHP\Console\Contracts\InputInterface;
use CommonPHP\Console\Contracts\OutputInterface as ConsoleOutputInterface;
use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;

final class ConsoleApplicationTest extends TestCase
{
    public function testItRegistersConsoleDefinitions(): void
    {
        $app = (new ConsoleApplication())
            ->callable('demo', static fn (): int => 0, 'Demo command');
        $builder = new ContainerBuilder();

        $app->configure($builder);
        $container = $builder->build();

        self::assertInstanceOf(InputInterface::class, $container->get(InputInterface::class));
        self::assertInstanceOf(ConsoleOutputInterface::class, $container->get(ConsoleOutputInterface::class));
        self::assertInstanceOf(CommandRunner::class, $container->get(CommandRunner::class));

        $registry = $container->get(CommandRegistry::class);

        self::assertTrue($registry->has('demo'));
        self::assertSame($registry, $app->commands());
    }

    public function testItRegistersCommandObjectsFluently(): void
    {
        $command = new ApplicationFixtureCommand();
        $app = new ConsoleApplication();

        self::assertSame($app, $app->command($command));
        self::assertSame($command, $app->commands()->get('app-fixture'));
    }
}

final class ApplicationFixtureCommand extends AbstractCommand
{
    protected function name(): string
    {
        return 'app-fixture';
    }

    protected function configure(CommandDefinition $definition): CommandDefinition
    {
        return $definition->describe('Application fixture');
    }

    public function handle(InputInterface $input, ConsoleOutputInterface $output): int
    {
        return 0;
    }
}

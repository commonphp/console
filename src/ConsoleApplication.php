<?php

declare(strict_types=1);

namespace CommonPHP\Console;

use CommonPHP\Console\Contracts\CommandInterface;
use CommonPHP\Console\Contracts\InputInterface;
use CommonPHP\Console\Contracts\OutputInterface;
use CommonPHP\Runtime\Contracts\ContainerConfiguratorInterface;
use CommonPHP\Runtime\Kernel;
use CommonPHP\Runtime\Support\InitializationContext;
use DI\ContainerBuilder;

use function DI\autowire;
use function DI\value;

class ConsoleApplication extends Kernel implements ContainerConfiguratorInterface
{
    private CommandRegistry $commands;

    public function __construct(?InitializationContext $context = null)
    {
        $this->commands = new CommandRegistry();

        parent::__construct($context);

        $this->setExecutive(ConsoleExecutive::class);
    }

    public function command(CommandInterface $command): static
    {
        $this->commands->register($command);

        return $this;
    }

    /**
     * @param callable(InputInterface, OutputInterface): (CommandResult|\CommonPHP\Console\Enums\ExitCode|int) $handler
     */
    public function callable(CommandDefinition|string $definition, callable $handler, ?string $description = null): static
    {
        $this->commands->callable($definition, $handler, $description);

        return $this;
    }

    public function commands(): CommandRegistry
    {
        return $this->commands;
    }

    public function configure(ContainerBuilder $builder): void
    {
        $registry = $this->commands;

        $builder->addDefinitions([
            InputInterface::class => static fn (): InputInterface => ConsoleInput::fromArgv(),
            OutputInterface::class => autowire(ConsoleOutput::class),
            CommandRegistry::class => value($registry),
            CommandRunner::class => autowire(CommandRunner::class),
        ]);
    }
}

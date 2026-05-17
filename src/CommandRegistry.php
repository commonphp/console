<?php

declare(strict_types=1);

namespace CommonPHP\Console;

use ArrayIterator;
use CommonPHP\Console\Contracts\CommandInterface;
use CommonPHP\Console\Contracts\InputInterface;
use CommonPHP\Console\Contracts\OutputInterface;
use CommonPHP\Console\Enums\ExitCode;
use CommonPHP\Console\Exceptions\CommandAlreadyRegisteredException;
use CommonPHP\Console\Exceptions\CommandNotFoundException;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<string, CommandInterface>
 */
class CommandRegistry implements Countable, IteratorAggregate
{
    /**
     * @var array<string, CommandInterface>
     */
    private array $commands = [];

    /**
     * @var array<string, string>
     */
    private array $aliases = [];

    public function register(CommandInterface $command): static
    {
        $definition = $command->definition();
        $name = $definition->name();

        if (isset($this->commands[$name]) || isset($this->aliases[$name])) {
            throw CommandAlreadyRegisteredException::forName($name);
        }

        foreach ($definition->aliases() as $alias) {
            if (isset($this->commands[$alias])) {
                throw CommandAlreadyRegisteredException::forName($alias);
            }

            if (isset($this->aliases[$alias])) {
                throw CommandAlreadyRegisteredException::forAlias($alias, $this->aliases[$alias]);
            }
        }

        $this->commands[$name] = $command;

        foreach ($definition->aliases() as $alias) {
            $this->aliases[$alias] = $name;
        }

        return $this;
    }

    /**
     * @param callable(InputInterface, OutputInterface): (CommandResult|ExitCode|int) $handler
     */
    public function callable(CommandDefinition|string $definition, callable $handler, ?string $description = null): static
    {
        $definition = $definition instanceof CommandDefinition
            ? $definition
            : new CommandDefinition($definition, $description ?? '');

        return $this->register(new class($definition, $handler) implements CommandInterface {
            public function __construct(
                private readonly CommandDefinition $definition,
                private readonly mixed $handler,
            ) {
            }

            public function definition(): CommandDefinition
            {
                return $this->definition;
            }

            public function handle(InputInterface $input, OutputInterface $output): CommandResult|ExitCode|int
            {
                return ($this->handler)($input, $output);
            }
        });
    }

    public function has(string $name): bool
    {
        return isset($this->commands[$name]) || isset($this->aliases[$name]);
    }

    public function get(string $name): CommandInterface
    {
        $name = $this->aliases[$name] ?? $name;

        return $this->commands[$name] ?? throw CommandNotFoundException::forName($name);
    }

    public function remove(string $name): static
    {
        $resolved = $this->aliases[$name] ?? $name;

        unset($this->commands[$resolved]);

        foreach ($this->aliases as $alias => $command) {
            if ($command === $resolved || $alias === $name) {
                unset($this->aliases[$alias]);
            }
        }

        return $this;
    }

    /**
     * @return array<string, CommandInterface>
     */
    public function all(): array
    {
        return $this->commands;
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        return array_keys($this->commands);
    }

    public function isEmpty(): bool
    {
        return $this->commands === [];
    }

    public function count(): int
    {
        return count($this->commands);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->commands);
    }
}

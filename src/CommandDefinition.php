<?php

declare(strict_types=1);

namespace CommonPHP\Console;

use CommonPHP\Console\Exceptions\InvalidCommandException;

class CommandDefinition
{
    /**
     * @var array<string, CommandArgument>
     */
    private array $arguments = [];

    /**
     * @var array<string, CommandOption>
     */
    private array $options = [];

    /**
     * @var array<string, CommandOption>
     */
    private array $shortcuts = [];

    /**
     * @var list<string>
     */
    private array $aliases = [];

    public function __construct(
        private readonly string $name,
        private string $description = '',
    ) {
        $this->validateCommandName($name);
    }

    public static function make(string $name, string $description = ''): self
    {
        return new self($name, $description);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function describe(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function alias(string $alias): static
    {
        $this->validateCommandName($alias);

        if ($alias === $this->name || in_array($alias, $this->aliases, true)) {
            return $this;
        }

        $this->aliases[] = $alias;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function aliases(): array
    {
        return $this->aliases;
    }

    public function addArgument(
        CommandArgument|string $argument,
        string $description = '',
        bool $required = false,
        mixed $default = null,
        bool $variadic = false,
    ): static {
        $argument = $argument instanceof CommandArgument
            ? $argument
            : new CommandArgument($argument, $description, $required, $default, $variadic);

        if (isset($this->arguments[$argument->name()])) {
            throw InvalidCommandException::because('Duplicate console argument "' . $argument->name() . '".');
        }

        foreach ($this->arguments as $existing) {
            if ($existing->isVariadic()) {
                throw InvalidCommandException::because('No arguments can be added after variadic argument "' . $existing->name() . '".');
            }

            if (!$existing->isRequired() && $argument->isRequired()) {
                throw InvalidCommandException::because('Required argument "' . $argument->name() . '" cannot follow an optional argument.');
            }
        }

        $this->arguments[$argument->name()] = $argument;

        return $this;
    }

    public function argument(
        CommandArgument|string $argument,
        string $description = '',
        bool $required = false,
        mixed $default = null,
        bool $variadic = false,
    ): static {
        return $this->addArgument($argument, $description, $required, $default, $variadic);
    }

    public function addOption(
        CommandOption|string $option,
        ?string $shortcut = null,
        string $description = '',
        bool $acceptsValue = false,
        bool $required = false,
        mixed $default = null,
        bool $repeatable = false,
    ): static {
        $option = $option instanceof CommandOption
            ? $option
            : new CommandOption($option, $shortcut, $description, $acceptsValue, $required, $default, $repeatable);

        if (isset($this->options[$option->name()])) {
            throw InvalidCommandException::because('Duplicate console option "' . $option->name() . '".');
        }

        if ($option->shortcut() !== null && isset($this->shortcuts[$option->shortcut()])) {
            throw InvalidCommandException::because('Duplicate console option shortcut "' . $option->shortcut() . '".');
        }

        $this->options[$option->name()] = $option;

        if ($option->shortcut() !== null) {
            $this->shortcuts[$option->shortcut()] = $option;
        }

        return $this;
    }

    public function option(
        CommandOption|string $option,
        ?string $shortcut = null,
        string $description = '',
        bool $acceptsValue = false,
        bool $required = false,
        mixed $default = null,
        bool $repeatable = false,
    ): static {
        return $this->addOption($option, $shortcut, $description, $acceptsValue, $required, $default, $repeatable);
    }

    /**
     * @return array<string, CommandArgument>
     */
    public function arguments(): array
    {
        return $this->arguments;
    }

    public function getArgument(string $name): ?CommandArgument
    {
        return $this->arguments[$name] ?? null;
    }

    /**
     * @return array<string, CommandOption>
     */
    public function options(): array
    {
        return $this->options;
    }

    public function getOption(string $name): ?CommandOption
    {
        return $this->options[$name] ?? null;
    }

    public function getOptionByShortcut(string $shortcut): ?CommandOption
    {
        return $this->shortcuts[$shortcut] ?? null;
    }

    public function usage(?string $script = null): string
    {
        $parts = array_filter([$script, $this->name], static fn (?string $part): bool => $part !== null && $part !== '');

        foreach ($this->options as $option) {
            $parts[] = $option->synopsis();
        }

        foreach ($this->arguments as $argument) {
            $parts[] = $argument->synopsis();
        }

        return implode(' ', $parts);
    }

    private function validateCommandName(string $name): void
    {
        if (!preg_match('/^[A-Za-z][A-Za-z0-9:_\-.]*$/', $name)) {
            throw InvalidCommandException::invalidName($name);
        }
    }
}

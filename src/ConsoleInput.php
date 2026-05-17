<?php

declare(strict_types=1);

namespace CommonPHP\Console;

use CommonPHP\Console\Contracts\InputInterface;
use CommonPHP\Console\Exceptions\InvalidConsoleArgumentException;

class ConsoleInput implements InputInterface
{
    /**
     * @param list<string> $rawArguments
     * @param array<string, mixed> $arguments
     * @param array<string, mixed> $options
     * @param array<string, bool> $providedOptions
     */
    public function __construct(
        private readonly ?string $script = null,
        private readonly ?string $commandName = null,
        private readonly array $rawArguments = [],
        private readonly array $arguments = [],
        private readonly array $options = [],
        private readonly array $providedOptions = [],
        private readonly ?CommandDefinition $definition = null,
    ) {
    }

    /**
     * @param list<string>|null $argv
     */
    public static function fromArgv(?array $argv = null): self
    {
        $argv ??= $_SERVER['argv'] ?? [];
        $argv = array_values(array_map('strval', $argv));
        $script = array_shift($argv);
        $commandName = null;

        if (isset($argv[0]) && !self::isOptionToken($argv[0])) {
            $commandName = array_shift($argv);
        }

        return new self($script, $commandName, $argv);
    }

    public function script(): ?string
    {
        return $this->script;
    }

    public function commandName(?string $default = null): ?string
    {
        return $this->commandName ?? $default;
    }

    public function rawArguments(): array
    {
        return $this->rawArguments;
    }

    public function arguments(): array
    {
        return $this->arguments;
    }

    public function argument(string $name, mixed $default = null): mixed
    {
        return $this->arguments[$name] ?? $default;
    }

    public function hasArgument(string $name): bool
    {
        return array_key_exists($name, $this->arguments) && $this->arguments[$name] !== null;
    }

    public function options(): array
    {
        return $this->options;
    }

    public function option(string $name, mixed $default = null): mixed
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    public function hasOption(string $name): bool
    {
        return isset($this->providedOptions[$name]);
    }

    public function flag(string $name): bool
    {
        $value = $this->option($name, false);

        if (is_bool($value)) {
            return $value;
        }

        if (is_array($value)) {
            return $value !== [];
        }

        return $value !== null && $value !== '' && $value !== 0 && $value !== '0';
    }

    public function bind(CommandDefinition $definition): static
    {
        if ($this->definition === $definition) {
            return $this;
        }

        [$arguments, $options, $providedOptions] = $this->parse($definition);

        return new static(
            $this->script,
            $this->commandName ?? $definition->name(),
            $this->rawArguments,
            $arguments,
            $options,
            $providedOptions,
            $definition,
        );
    }

    public function wantsHelp(): bool
    {
        return in_array('--help', $this->rawArguments, true) || in_array('-h', $this->rawArguments, true);
    }

    /**
     * @return array{0: array<string, mixed>, 1: array<string, mixed>, 2: array<string, bool>}
     */
    private function parse(CommandDefinition $definition): array
    {
        $positionals = [];
        $options = $this->defaultOptions($definition);
        $providedOptions = [];
        $tokens = $this->rawArguments;

        for ($index = 0, $count = count($tokens); $index < $count; $index++) {
            $token = $tokens[$index];

            if ($token === '--') {
                array_push($positionals, ...array_slice($tokens, $index + 1));
                break;
            }

            if (str_starts_with($token, '--') && $token !== '--') {
                $this->readLongOption($definition, $tokens, $index, $token, $options, $providedOptions);
                continue;
            }

            if (str_starts_with($token, '-') && $token !== '-') {
                $this->readShortOptions($definition, $tokens, $index, $token, $options, $providedOptions);
                continue;
            }

            $positionals[] = $token;
        }

        return [$this->bindArguments($definition, $positionals), $this->validateOptions($definition, $options), $providedOptions];
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultOptions(CommandDefinition $definition): array
    {
        $options = [];

        foreach ($definition->options() as $option) {
            $options[$option->name()] = $option->isRepeatable()
                ? (array) ($option->default() ?? [])
                : ($option->default() ?? ($option->acceptsValue() ? null : false));
        }

        return $options;
    }

    /**
     * @param list<string> $tokens
     * @param array<string, mixed> $options
     * @param array<string, bool> $providedOptions
     */
    private function readLongOption(
        CommandDefinition $definition,
        array $tokens,
        int &$index,
        string $token,
        array &$options,
        array &$providedOptions,
    ): void {
        $payload = substr($token, 2);
        [$name, $value] = array_pad(explode('=', $payload, 2), 2, null);
        $negated = false;

        if (str_starts_with($name, 'no-')) {
            $possibleName = substr($name, 3);
            $possibleOption = $definition->getOption($possibleName);

            if ($possibleOption !== null && !$possibleOption->acceptsValue()) {
                $name = $possibleName;
                $negated = true;
            }
        }

        $option = $definition->getOption($name) ?? throw InvalidConsoleArgumentException::unknownOption('--' . $name);

        if ($option->acceptsValue()) {
            if ($value === null) {
                $index++;
                $value = $tokens[$index] ?? throw InvalidConsoleArgumentException::missingOptionValue('--' . $name);
            }
        } elseif ($value !== null) {
            throw InvalidConsoleArgumentException::because('Console option "--' . $name . '" does not accept a value.');
        } else {
            $value = !$negated;
        }

        $this->setOption($option, $value, $options, $providedOptions);
    }

    /**
     * @param list<string> $tokens
     * @param array<string, mixed> $options
     * @param array<string, bool> $providedOptions
     */
    private function readShortOptions(
        CommandDefinition $definition,
        array $tokens,
        int &$index,
        string $token,
        array &$options,
        array &$providedOptions,
    ): void {
        $payload = substr($token, 1);
        $length = strlen($payload);

        for ($offset = 0; $offset < $length; $offset++) {
            $shortcut = $payload[$offset];
            $option = $definition->getOptionByShortcut($shortcut)
                ?? throw InvalidConsoleArgumentException::unknownOption('-' . $shortcut);

            if (!$option->acceptsValue()) {
                $this->setOption($option, true, $options, $providedOptions);
                continue;
            }

            $value = substr($payload, $offset + 1);

            if (str_starts_with($value, '=')) {
                $value = substr($value, 1);
            }

            if ($value === '') {
                $index++;
                $value = $tokens[$index] ?? throw InvalidConsoleArgumentException::missingOptionValue('-' . $shortcut);
            }

            $this->setOption($option, $value, $options, $providedOptions);
            break;
        }
    }

    /**
     * @param array<string, mixed> $options
     * @param array<string, bool> $providedOptions
     */
    private function setOption(CommandOption $option, mixed $value, array &$options, array &$providedOptions): void
    {
        if ($option->isRepeatable()) {
            $options[$option->name()][] = $value;
        } else {
            $options[$option->name()] = $value;
        }

        $providedOptions[$option->name()] = true;
    }

    /**
     * @param list<string> $positionals
     * @return array<string, mixed>
     */
    private function bindArguments(CommandDefinition $definition, array $positionals): array
    {
        $arguments = [];
        $offset = 0;

        foreach ($definition->arguments() as $argument) {
            if ($argument->isVariadic()) {
                $values = array_slice($positionals, $offset);

                if ($values === [] && $argument->isRequired()) {
                    throw InvalidConsoleArgumentException::missingArgument($argument->name());
                }

                $arguments[$argument->name()] = $values === [] ? ($argument->default() ?? []) : $values;
                $offset = count($positionals);
                continue;
            }

            if (array_key_exists($offset, $positionals)) {
                $arguments[$argument->name()] = $positionals[$offset];
                $offset++;
                continue;
            }

            if ($argument->isRequired()) {
                throw InvalidConsoleArgumentException::missingArgument($argument->name());
            }

            $arguments[$argument->name()] = $argument->default();
        }

        if (array_key_exists($offset, $positionals)) {
            throw InvalidConsoleArgumentException::unexpectedArgument($positionals[$offset]);
        }

        return $arguments;
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function validateOptions(CommandDefinition $definition, array $options): array
    {
        foreach ($definition->options() as $option) {
            $value = $options[$option->name()] ?? null;

            if ($option->isRequired() && ($value === null || $value === [] || $value === false)) {
                throw InvalidConsoleArgumentException::missingOptionValue('--' . $option->name());
            }
        }

        return $options;
    }

    private static function isOptionToken(string $token): bool
    {
        return str_starts_with($token, '-') && $token !== '-';
    }
}

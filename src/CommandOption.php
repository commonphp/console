<?php

declare(strict_types=1);

namespace CommonPHP\Console;

use CommonPHP\Console\Exceptions\InvalidCommandException;

class CommandOption
{
    public function __construct(
        private readonly string $name,
        private readonly ?string $shortcut = null,
        private readonly string $description = '',
        private readonly bool $acceptsValue = false,
        private readonly bool $required = false,
        private readonly mixed $default = null,
        private readonly bool $repeatable = false,
    ) {
        $this->validateName($name);

        if ($shortcut !== null && !preg_match('/^[A-Za-z0-9]$/', $shortcut)) {
            throw InvalidCommandException::because('Console option shortcut must be one alphanumeric character.');
        }

        if ($repeatable && !$acceptsValue) {
            throw InvalidCommandException::because('Only value options can be repeatable.');
        }
    }

    public static function flag(
        string $name,
        ?string $shortcut = null,
        string $description = '',
        bool $default = false,
    ): self {
        return new self($name, $shortcut, $description, false, false, $default);
    }

    public static function value(
        string $name,
        ?string $shortcut = null,
        string $description = '',
        bool $required = false,
        mixed $default = null,
        bool $repeatable = false,
    ): self {
        return new self($name, $shortcut, $description, true, $required, $default, $repeatable);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function shortcut(): ?string
    {
        return $this->shortcut;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function acceptsValue(): bool
    {
        return $this->acceptsValue;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function default(): mixed
    {
        return $this->default;
    }

    public function isRepeatable(): bool
    {
        return $this->repeatable;
    }

    public function synopsis(): string
    {
        $option = '--' . $this->name;

        if ($this->acceptsValue) {
            $option .= '=VALUE';
        }

        return $this->required ? $option : '[' . $option . ']';
    }

    public function label(): string
    {
        $label = '--' . $this->name;

        if ($this->shortcut !== null) {
            $label = '-' . $this->shortcut . ', ' . $label;
        }

        return $this->acceptsValue ? $label . '=VALUE' : $label;
    }

    private function validateName(string $name): void
    {
        if (!preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/', $name)) {
            throw InvalidCommandException::because('Invalid console option name "' . $name . '".');
        }
    }
}

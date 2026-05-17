<?php

declare(strict_types=1);

namespace CommonPHP\Console;

use CommonPHP\Console\Exceptions\InvalidCommandException;

class CommandArgument
{
    public function __construct(
        private readonly string $name,
        private readonly string $description = '',
        private readonly bool $required = false,
        private readonly mixed $default = null,
        private readonly bool $variadic = false,
    ) {
        $this->validateName($name);

        if ($this->variadic && $this->default !== null && !is_array($this->default)) {
            throw InvalidCommandException::because('Variadic argument "' . $name . '" must use an array default.');
        }
    }

    public static function required(string $name, string $description = ''): self
    {
        return new self($name, $description, true);
    }

    public static function optional(string $name, string $description = '', mixed $default = null): self
    {
        return new self($name, $description, false, $default);
    }

    public static function variadic(string $name, string $description = '', bool $required = false): self
    {
        return new self($name, $description, $required, [], true);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function default(): mixed
    {
        return $this->default;
    }

    public function isVariadic(): bool
    {
        return $this->variadic;
    }

    public function synopsis(): string
    {
        if ($this->variadic) {
            $name = $this->name . '...';

            return $this->required ? '<' . $name . '>' : '[' . $name . ']';
        }

        return $this->required ? '<' . $this->name . '>' : '[' . $this->name . ']';
    }

    private function validateName(string $name): void
    {
        if (!preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/', $name)) {
            throw InvalidCommandException::because('Invalid console argument name "' . $name . '".');
        }
    }
}

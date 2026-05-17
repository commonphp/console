<?php

declare(strict_types=1);

namespace CommonPHP\Console\Contracts;

use CommonPHP\Console\CommandDefinition;

interface InputInterface
{
    public function script(): ?string;

    public function commandName(?string $default = null): ?string;

    /**
     * @return list<string>
     */
    public function rawArguments(): array;

    /**
     * @return array<string, mixed>
     */
    public function arguments(): array;

    public function argument(string $name, mixed $default = null): mixed;

    public function hasArgument(string $name): bool;

    /**
     * @return array<string, mixed>
     */
    public function options(): array;

    public function option(string $name, mixed $default = null): mixed;

    public function hasOption(string $name): bool;

    public function flag(string $name): bool;

    public function bind(CommandDefinition $definition): static;
}

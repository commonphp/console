<?php

declare(strict_types=1);

namespace CommonPHP\Console\Contracts;

use CommonPHP\Console\CommandDefinition;

abstract class AbstractCommand implements CommandInterface
{
    private ?CommandDefinition $definition = null;

    final public function definition(): CommandDefinition
    {
        return $this->definition ??= $this->configure(
            new CommandDefinition($this->name(), $this->description()),
        );
    }

    protected function name(): string
    {
        $class = str_replace('\\', '/', static::class);
        $short = basename($class);
        $short = preg_replace('/Command$/', '', $short) ?: $short;
        $name = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '-$0', $short));

        return trim($name, '-');
    }

    protected function description(): string
    {
        return '';
    }

    protected function configure(CommandDefinition $definition): CommandDefinition
    {
        return $definition;
    }
}

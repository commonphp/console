<?php

declare(strict_types=1);

namespace CommonPHP\Console\Contracts;

use CommonPHP\Console\CommandDefinition;
use CommonPHP\Console\CommandResult;
use CommonPHP\Console\Enums\ExitCode;

interface CommandInterface
{
    public function definition(): CommandDefinition;

    public function handle(InputInterface $input, OutputInterface $output): CommandResult|ExitCode|int;
}

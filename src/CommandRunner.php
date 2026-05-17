<?php

declare(strict_types=1);

namespace CommonPHP\Console;

use CommonPHP\Console\Contracts\CommandInterface;
use CommonPHP\Console\Contracts\InputInterface;
use CommonPHP\Console\Contracts\OutputInterface;
use CommonPHP\Console\Exceptions\CommandExecutionException;
use CommonPHP\Console\Exceptions\CommandNotFoundException;
use CommonPHP\Console\Exceptions\ConsoleException;
use Throwable;

class CommandRunner
{
    public function __construct(
        private readonly ?CommandRegistry $registry = null,
    ) {
    }

    public function registry(): CommandRegistry
    {
        return $this->registry ?? new CommandRegistry();
    }

    /**
     * @param list<string>|InputInterface|null $input
     */
    public function run(array|InputInterface|null $input = null, ?OutputInterface $output = null): CommandResult
    {
        $input = $this->normalizeInput($input);
        $output ??= new ConsoleOutput();
        $name = $input->commandName();

        if ($name === null) {
            throw CommandNotFoundException::missing();
        }

        $command = $this->registry()->get($name);

        return $this->execute($command, $input, $output);
    }

    private function execute(CommandInterface $command, InputInterface $input, OutputInterface $output): CommandResult
    {
        $definition = $command->definition();

        try {
            $result = $command->handle($input->bind($definition), $output);
        } catch (ConsoleException $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            throw CommandExecutionException::forCommand($definition->name(), $throwable);
        }

        return CommandResult::from($result);
    }

    /**
     * @param list<string>|InputInterface|null $input
     */
    private function normalizeInput(array|InputInterface|null $input): InputInterface
    {
        if ($input instanceof InputInterface) {
            return $input;
        }

        return ConsoleInput::fromArgv($input);
    }
}

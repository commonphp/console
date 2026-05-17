<?php

declare(strict_types=1);

namespace CommonPHP\Console;

use CommonPHP\Console\Contracts\InputInterface;
use CommonPHP\Console\Contracts\OutputInterface;
use CommonPHP\Console\Enums\ExitCode;
use CommonPHP\Console\Exceptions\CommandExecutionException;
use CommonPHP\Console\Exceptions\CommandNotFoundException;
use CommonPHP\Console\Exceptions\ConsoleException;
use CommonPHP\Console\Exceptions\InvalidConsoleArgumentException;
use CommonPHP\Runtime\Contracts\ExecutiveInterface;
use CommonPHP\Runtime\Contracts\ServiceProviderInterface;
use DI\ContainerBuilder;

use function DI\autowire;

class ConsoleExecutive implements ExecutiveInterface, ServiceProviderInterface
{
    public function __construct(
        private readonly ?CommandRegistry $registry = null,
        private readonly ?CommandRunner $runner = null,
        private readonly ?InputInterface $input = null,
        private readonly ?OutputInterface $output = null,
    ) {
    }

    public function configure(ContainerBuilder $builder): void
    {
        $builder->addDefinitions([
            InputInterface::class => static fn (): InputInterface => ConsoleInput::fromArgv(),
            OutputInterface::class => autowire(ConsoleOutput::class),
            CommandRegistry::class => autowire(CommandRegistry::class),
            CommandRunner::class => autowire(CommandRunner::class),
        ]);
    }

    public function execute(): int
    {
        $registry = $this->registry ?? $this->runner?->registry() ?? new CommandRegistry();
        $runner = $this->runner ?? new CommandRunner($registry);
        $input = $this->input ?? ConsoleInput::fromArgv();
        $output = $this->output ?? new ConsoleOutput();
        $commandName = $input->commandName();

        try {
            if ($this->shouldRenderList($input, $registry)) {
                $this->renderCommandList($registry, $output);

                return ExitCode::SUCCESS->value;
            }

            if ($this->shouldRenderHelpCommand($input, $registry)) {
                $target = $input->rawArguments()[0] ?? null;

                if ($target === null) {
                    $this->renderCommandList($registry, $output);
                } else {
                    $this->renderCommandHelp($registry->get($target)->definition(), $input, $output);
                }

                return ExitCode::SUCCESS->value;
            }

            if ($input instanceof ConsoleInput && $input->wantsHelp() && $commandName !== null && $registry->has($commandName)) {
                $this->renderCommandHelp($registry->get($commandName)->definition(), $input, $output);

                return ExitCode::SUCCESS->value;
            }

            $result = $runner->run($input, $output);
            $this->renderResult($result, $output);

            return $result->exitCode();
        } catch (CommandNotFoundException $exception) {
            $output->errorln($exception->getMessage());

            return ExitCode::COMMAND_NOT_FOUND->value;
        } catch (InvalidConsoleArgumentException $exception) {
            $output->errorln($exception->getMessage());

            return ExitCode::INVALID_ARGUMENT->value;
        } catch (CommandExecutionException $exception) {
            $output->errorln($exception->getMessage());

            return ExitCode::EXCEPTION->value;
        } catch (ConsoleException $exception) {
            $output->errorln($exception->getMessage());

            return ExitCode::FAILURE->value;
        }
    }

    private function shouldRenderList(InputInterface $input, CommandRegistry $registry): bool
    {
        $name = $input->commandName();

        if ($name === null) {
            return true;
        }

        return $name === 'list' && !$registry->has('list');
    }

    private function shouldRenderHelpCommand(InputInterface $input, CommandRegistry $registry): bool
    {
        return $input->commandName() === 'help' && !$registry->has('help');
    }

    private function renderCommandList(CommandRegistry $registry, OutputInterface $output): void
    {
        if ($registry->isEmpty()) {
            $output->writeln('No console commands registered.');

            return;
        }

        $output->writeln('Available commands:');

        foreach ($registry->all() as $command) {
            $definition = $command->definition();
            $line = '  ' . str_pad($definition->name(), 24);

            if ($definition->description() !== '') {
                $line .= $definition->description();
            }

            $output->writeln(rtrim($line));
        }
    }

    private function renderCommandHelp(CommandDefinition $definition, InputInterface $input, OutputInterface $output): void
    {
        $script = $input->script() ?? 'console';

        $output->writeln('Usage:');
        $output->writeln('  ' . $definition->usage($script));

        if ($definition->description() !== '') {
            $output->newLine();
            $output->writeln('Description:');
            $output->writeln('  ' . $definition->description());
        }

        if ($definition->arguments() !== []) {
            $output->newLine();
            $output->writeln('Arguments:');

            foreach ($definition->arguments() as $argument) {
                $output->writeln('  ' . str_pad($argument->name(), 20) . $argument->description());
            }
        }

        if ($definition->options() !== []) {
            $output->newLine();
            $output->writeln('Options:');

            foreach ($definition->options() as $option) {
                $output->writeln('  ' . str_pad($option->label(), 20) . $option->description());
            }
        }
    }

    private function renderResult(CommandResult $result, OutputInterface $output): void
    {
        if ($result->message() !== '') {
            $output->writeln($result->message());
        }

        if ($result->error() !== '') {
            $output->errorln($result->error());
        }
    }
}

<?php

declare(strict_types=1);

namespace CommonPHP\Console\Tests\Unit;

use CommonPHP\Console\CommandDefinition;
use CommonPHP\Console\CommandResult;
use CommonPHP\Console\ConsoleInput;
use CommonPHP\Console\ConsoleOutput;
use CommonPHP\Console\Contracts\AbstractCommand;
use CommonPHP\Console\Contracts\InputInterface;
use CommonPHP\Console\Contracts\OutputInterface;
use PHPUnit\Framework\TestCase;

final class AbstractCommandTest extends TestCase
{
    public function testItBuildsAndCachesDefinitionsFromTemplateMethods(): void
    {
        $command = new SampleReportCommand();

        $definition = $command->definition();

        self::assertSame($definition, $command->definition());
        self::assertSame('sample-report', $definition->name());
        self::assertSame('Render sample report', $definition->description());
        self::assertSame(['target'], array_keys($definition->arguments()));
    }

    public function testConcreteCommandsCanHandleInputAndOutput(): void
    {
        $command = new SampleReportCommand();
        $output = ConsoleOutput::buffered();
        $input = ConsoleInput::fromArgv(['cli', 'sample-report', 'daily'])->bind($command->definition());

        $result = $command->handle($input, $output);

        self::assertInstanceOf(CommandResult::class, $result);
        self::assertSame('daily' . PHP_EOL, $output->fetch());
    }
}

final class SampleReportCommand extends AbstractCommand
{
    protected function description(): string
    {
        return 'Render sample report';
    }

    protected function configure(CommandDefinition $definition): CommandDefinition
    {
        return $definition->argument('target', 'Report target', true);
    }

    public function handle(InputInterface $input, OutputInterface $output): CommandResult
    {
        $output->writeln((string) $input->argument('target'));

        return CommandResult::success();
    }
}

# Commands

Commands are small objects or callables that receive console input, write to console output, and return an exit result.

Related pages:

- [Input and output](input-output.md)
- [Error handling](error-handling.md)
- [Runtime integration](runtime-integration.md)

## Command Contract

```php
namespace CommonPHP\Console\Contracts;

use CommonPHP\Console\CommandDefinition;
use CommonPHP\Console\CommandResult;
use CommonPHP\Console\Enums\ExitCode;

interface CommandInterface
{
    public function definition(): CommandDefinition;

    public function handle(InputInterface $input, OutputInterface $output): CommandResult|ExitCode|int;
}
```

`definition()` describes the command name, aliases, arguments, options, and usage text. `handle()` performs the work.

## Abstract Commands

`AbstractCommand` is a small convenience base class. Override `name()`, `description()`, and `configure()` as needed. The definition is built lazily and cached.

```php
use CommonPHP\Console\CommandDefinition;
use CommonPHP\Console\CommandResult;
use CommonPHP\Console\Contracts\AbstractCommand;
use CommonPHP\Console\Contracts\InputInterface;
use CommonPHP\Console\Contracts\OutputInterface;

final class ReportCommand extends AbstractCommand
{
    protected function name(): string
    {
        return 'report:daily';
    }

    protected function description(): string
    {
        return 'Generate the daily report.';
    }

    protected function configure(CommandDefinition $definition): CommandDefinition
    {
        return $definition->argument('date', 'Report date', required: false);
    }

    public function handle(InputInterface $input, OutputInterface $output): CommandResult
    {
        $output->writeln('Report date: ' . ($input->argument('date') ?? 'today'));

        return CommandResult::success();
    }
}
```

If `name()` is not overridden, `AbstractCommand` derives a kebab-case name from the class name and removes a trailing `Command` suffix.

## Command Definitions

```php
use CommonPHP\Console\CommandArgument;
use CommonPHP\Console\CommandDefinition;
use CommonPHP\Console\CommandOption;

$definition = (new CommandDefinition('cache:clear', 'Clear application cache'))
    ->alias('cache-clear')
    ->argument(CommandArgument::optional('store', 'Store name', 'default'))
    ->option(CommandOption::flag('force', 'f', 'Skip confirmation'))
    ->option(CommandOption::value('tag', 't', 'Cache tag', repeatable: true));
```

Command names and aliases may contain letters, numbers, `:`, `_`, `-`, and `.`. They must start with a letter.

## Arguments

Arguments are positional:

- required arguments use `<name>` in usage text;
- optional arguments use `[name]`;
- variadic arguments collect the remaining positional values.

Required arguments cannot follow optional arguments, and no argument can follow a variadic argument.

## Options

Options are named values:

- flags are boolean switches such as `--force` or `-f`;
- value options accept a value such as `--env=prod`, `--env prod`, `-eprod`, or `-e prod`;
- repeatable value options collect all provided values into an array.

Flags can be negated with `--no-name` when the option exists and does not accept a value.

## Registry

`CommandRegistry` stores commands by name and alias.

```php
$registry = new CommandRegistry();
$registry->register(new ReportCommand());
$registry->callable('status', static fn ($input, $output): int => 0, 'Show status');
```

Duplicate command names, duplicate aliases, and aliases that collide with command names are rejected.

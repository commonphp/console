# Usage

The package has three common usage styles: direct command runner use, `ConsoleExecutive` use, and `ConsoleApplication` use.

## Command Runner

Use `CommandRunner` for tests, embedded CLI tools, and cases where the caller wants to catch console exceptions directly.

```php
use CommonPHP\Console\CommandRegistry;
use CommonPHP\Console\CommandRunner;
use CommonPHP\Console\ConsoleInput;
use CommonPHP\Console\ConsoleOutput;

$registry = new CommandRegistry();
$registry->callable('status', static function ($input, $output): int {
    $output->writeln('OK');

    return 0;
});

$result = (new CommandRunner($registry))->run(
    ConsoleInput::fromArgv(['console', 'status']),
    new ConsoleOutput(),
);
```

## Console Executive

Use `ConsoleExecutive` when you want command-line errors translated to exit codes and stderr.

```php
use CommonPHP\Console\ConsoleExecutive;
use CommonPHP\Console\ConsoleInput;

$status = (new ConsoleExecutive(
    registry: $registry,
    input: ConsoleInput::fromArgv($_SERVER['argv']),
))->execute();
```

With no command, the executive prints the command list. Unless an application registers its own `list` or `help` commands, those names are handled by the executive.

## Console Application

Use `ConsoleApplication` when commands should run inside CommonPHP Runtime with service providers, modules, context, environment loading, and dependency injection.

```php
use CommonPHP\Console\ConsoleApplication;

$app = new ConsoleApplication();

$app->callable('status', static function ($input, $output): int {
    $output->writeln('OK');

    return 0;
});

$app->run();
```

## Command Results

Commands may return:

- `CommandResult`
- `ExitCode`
- any non-negative integer exit code

`CommandResult` can carry a normal message and an error message. `ConsoleExecutive` writes the normal message to stdout and the error message to stderr.

```php
use CommonPHP\Console\CommandResult;
use CommonPHP\Console\Enums\ExitCode;

return new CommandResult(ExitCode::FAILURE, error: 'The operation failed.');
```

# Getting Started

CommonPHP Console can be used directly with `CommandRunner` or through `ConsoleApplication` when the command tool should run inside CommonPHP Runtime.

## Define A Callable Command

```php
use CommonPHP\Console\CommandArgument;
use CommonPHP\Console\CommandDefinition;
use CommonPHP\Console\CommandOption;
use CommonPHP\Console\CommandRegistry;
use CommonPHP\Console\CommandResult;

$registry = new CommandRegistry();

$registry->callable(
    (new CommandDefinition('greet', 'Print a greeting'))
        ->alias('hello')
        ->argument(CommandArgument::required('name', 'Name to greet'))
        ->option(CommandOption::flag('loud', 'l', 'Uppercase the name')),
    static function ($input, $output): CommandResult {
        $name = (string) $input->argument('name');

        if ($input->flag('loud')) {
            $name = strtoupper($name);
        }

        $output->writeln('Hello ' . $name);

        return CommandResult::success();
    },
);
```

## Run A Command

```php
use CommonPHP\Console\CommandRunner;
use CommonPHP\Console\ConsoleInput;
use CommonPHP\Console\ConsoleOutput;

$input = ConsoleInput::fromArgv(['console', 'greet', 'Ada', '--loud']);
$output = new ConsoleOutput();

$result = (new CommandRunner($registry))->run($input, $output);

exit($result->exitCode());
```

`CommandRunner` throws package exceptions for missing commands, invalid input, and command execution failures. Use `ConsoleExecutive` or `ConsoleApplication` when you want those exceptions translated into terminal output and exit codes.

## Runtime Application

```php
use CommonPHP\Console\ConsoleApplication;
use CommonPHP\Runtime\Support\InitializationContext;

$app = new ConsoleApplication(new InitializationContext(root: dirname(__DIR__)));

$app->callable('greet', static function ($input, $output): int {
    $output->writeln('Hello from runtime');

    return 0;
});

exit($app->execute());
```

`ConsoleApplication` sets `ConsoleExecutive` as the runtime executive and registers console services with the runtime container.

# Example: Callable Command

Callable commands are useful for small tools, tests, and package demos.

Related pages:

- [Usage](../usage.md)
- [Commands](../commands.md)
- [Input and output](../input-output.md)

```php
<?php

declare(strict_types=1);

use CommonPHP\Console\CommandArgument;
use CommonPHP\Console\CommandDefinition;
use CommonPHP\Console\CommandOption;
use CommonPHP\Console\CommandRegistry;
use CommonPHP\Console\CommandResult;
use CommonPHP\Console\CommandRunner;
use CommonPHP\Console\ConsoleInput;
use CommonPHP\Console\ConsoleOutput;

require __DIR__ . '/vendor/autoload.php';

$registry = new CommandRegistry();

$registry->callable(
    (new CommandDefinition('greet', 'Print a greeting'))
        ->argument(CommandArgument::required('name', 'Name to greet'))
        ->option(CommandOption::flag('loud', 'l', 'Uppercase the greeting')),
    static function ($input, $output): CommandResult {
        $name = (string) $input->argument('name');

        if ($input->flag('loud')) {
            $name = strtoupper($name);
        }

        $output->writeln('Hello ' . $name);

        return CommandResult::success();
    },
);

$result = (new CommandRunner($registry))->run(
    ConsoleInput::fromArgv($_SERVER['argv']),
    new ConsoleOutput(),
);

exit($result->exitCode());
```

Run it:

```bash
php console.php greet Ada --loud
```

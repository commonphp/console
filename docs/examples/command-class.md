# Example: Command Class

Command classes are easier to test and update as behavior grows.

Related pages:

- [Commands](../commands.md)
- [Error handling](../error-handling.md)

```php
<?php

declare(strict_types=1);

namespace App\Console;

use CommonPHP\Console\CommandDefinition;
use CommonPHP\Console\CommandResult;
use CommonPHP\Console\Contracts\AbstractCommand;
use CommonPHP\Console\Contracts\InputInterface;
use CommonPHP\Console\Contracts\OutputInterface;

final class ExportUsersCommand extends AbstractCommand
{
    protected function name(): string
    {
        return 'users:export';
    }

    protected function description(): string
    {
        return 'Export users to a CSV file.';
    }

    protected function configure(CommandDefinition $definition): CommandDefinition
    {
        return $definition
            ->argument('path', 'Destination CSV path', required: true)
            ->option('include-disabled', description: 'Include disabled users');
    }

    public function handle(InputInterface $input, OutputInterface $output): CommandResult
    {
        $path = (string) $input->argument('path');

        $output->writeln('Exporting users to ' . $path);

        return CommandResult::success('Export complete.');
    }
}
```

Register it:

```php
use App\Console\ExportUsersCommand;
use CommonPHP\Console\CommandRegistry;

$registry = (new CommandRegistry())->register(new ExportUsersCommand());
```

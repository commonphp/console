# Runtime Integration

Console integrates with CommonPHP Runtime through `ConsoleExecutive` and `ConsoleApplication`.

Related pages:

- [Commands](commands.md)
- [Usage](usage.md)
- [Error handling](error-handling.md)

## ConsoleExecutive

`ConsoleExecutive` implements Runtime's `ExecutiveInterface`. It reads a command from input, runs it through `CommandRunner`, renders result messages, catches console exceptions, and returns an integer status code.

Default behavior:

- no command prints the command list;
- `list` prints the command list unless an application registered a `list` command;
- `help` prints command help unless an application registered a `help` command;
- `--help` and `-h` print help for the selected command.

## ConsoleApplication

`ConsoleApplication` extends Runtime's `Kernel` and sets `ConsoleExecutive` as the executive.

```php
use CommonPHP\Console\ConsoleApplication;

$app = new ConsoleApplication();

$app->callable('status', static function ($input, $output): int {
    $output->writeln('OK');

    return 0;
});

$app->execute();
```

`ConsoleApplication` registers these definitions with the runtime container:

- `InputInterface`
- `OutputInterface`
- `CommandRegistry`
- `CommandRunner`

The same registry instance returned by `$app->commands()` is injected into the runtime container.

## Exit Codes

`ExitCode` provides common statuses:

- `SUCCESS`: `0`
- `FAILURE`: `1`
- `INVALID_ARGUMENT`: `2`
- `COMMAND_NOT_FOUND`: `127`
- `EXCEPTION`: `2147483647`

Commands may return these enum values directly, or return any non-negative integer status.

# Error Handling

Console separates invalid command setup, invalid user input, missing commands, and command execution failures.

## Exceptions

Base exception:

- `ConsoleException`

Definition and registry exceptions:

- `InvalidCommandException`
- `CommandAlreadyRegisteredException`

Runtime input and execution exceptions:

- `InvalidConsoleArgumentException`
- `CommandNotFoundException`
- `CommandExecutionException`

## CommandRunner Behavior

`CommandRunner` is strict. It throws exceptions instead of rendering output.

- missing command name: `CommandNotFoundException`;
- unknown command name: `CommandNotFoundException`;
- invalid input while binding: `InvalidConsoleArgumentException`;
- console exceptions thrown by commands: passed through unchanged;
- other throwables from commands: wrapped in `CommandExecutionException`.

Use this style when tests or application code need direct exception control.

## ConsoleExecutive Behavior

`ConsoleExecutive` catches console exceptions and returns stable exit codes:

- `CommandNotFoundException`: `ExitCode::COMMAND_NOT_FOUND`
- `InvalidConsoleArgumentException`: `ExitCode::INVALID_ARGUMENT`
- `CommandExecutionException`: `ExitCode::EXCEPTION`
- other `ConsoleException`: `ExitCode::FAILURE`

The executive writes exception messages to stderr.

## Command Results

Commands should return failure results for expected operational failures.

```php
use CommonPHP\Console\CommandResult;
use CommonPHP\Console\Enums\ExitCode;

return CommandResult::failure('Unable to write export file.', ExitCode::FAILURE);
```

Throw exceptions when command setup is invalid or when an unexpected failure should be escalated to the executive.

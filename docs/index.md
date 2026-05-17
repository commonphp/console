# CommonPHP Console Documentation

CommonPHP Console is the command-line runtime package for CommonPHP applications and plain PHP CLI tools. It provides command definitions, command registration, argument and option parsing, input/output abstractions, a command runner, a runtime executive, and a `ConsoleApplication` wrapper for CommonPHP Runtime.

Console is intentionally small. It owns command-line concerns and leaves application bootstrapping to `comphp/runtime`, persistence to database packages, rendering to UI packages, and request/response handling to HTTP or API packages.

## Start Here

- [Getting started](getting-started.md)
- [Usage](usage.md)
- [Package boundaries](package-boundaries.md)

## Console Concepts

- [Commands](commands.md)
- [Input and output](input-output.md)
- [Runtime integration](runtime-integration.md)
- [Error handling](error-handling.md)

## Examples

- [Examples index](examples/index.md)
- [Callable command](examples/callable-command.md)
- [Command class](examples/command-class.md)
- [Runtime application](examples/runtime-application.md)

## Development

- [Testing and QA](testing.md)

## Public API Map

Entry points:

- `CommonPHP\Console\ConsoleApplication`
- `CommonPHP\Console\ConsoleExecutive`
- `CommonPHP\Console\CommandRunner`
- `CommonPHP\Console\CommandRegistry`

Command model:

- `CommonPHP\Console\CommandDefinition`
- `CommonPHP\Console\CommandArgument`
- `CommonPHP\Console\CommandOption`
- `CommonPHP\Console\CommandResult`
- `CommonPHP\Console\Enums\ExitCode`

Input and output:

- `CommonPHP\Console\ConsoleInput`
- `CommonPHP\Console\ConsoleOutput`

Contracts:

- `CommonPHP\Console\Contracts\CommandInterface`
- `CommonPHP\Console\Contracts\AbstractCommand`
- `CommonPHP\Console\Contracts\InputInterface`
- `CommonPHP\Console\Contracts\OutputInterface`

Exceptions:

- `CommonPHP\Console\Exceptions\ConsoleException`
- `CommonPHP\Console\Exceptions\CommandAlreadyRegisteredException`
- `CommonPHP\Console\Exceptions\CommandExecutionException`
- `CommonPHP\Console\Exceptions\CommandNotFoundException`
- `CommonPHP\Console\Exceptions\InvalidCommandException`
- `CommonPHP\Console\Exceptions\InvalidConsoleArgumentException`

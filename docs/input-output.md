# Input And Output

`ConsoleInput` adapts an argv array into command name, raw tokens, bound positional arguments, and bound options. `ConsoleOutput` wraps stdout and stderr streams.

## Input From Argv

```php
use CommonPHP\Console\ConsoleInput;

$input = ConsoleInput::fromArgv(['console', 'deploy', 'prod', '--force']);

$input->script();       // console
$input->commandName();  // deploy
$input->rawArguments(); // ['prod', '--force']
```

If no argv array is passed, `ConsoleInput::fromArgv()` reads `$_SERVER['argv']`.

## Binding To A Definition

Raw input is bound to a `CommandDefinition` before a command handles it.

```php
$bound = $input->bind($definition);

$bound->argument('target');
$bound->option('env', 'dev');
$bound->flag('force');
```

Binding validates required arguments, required options, unknown options, missing option values, and unexpected positional values.

## Parsing Rules

Supported option forms:

- `--flag`
- `--no-flag`
- `--name=value`
- `--name value`
- `-f`
- `-abc` for stacked short flags
- `-eprod` or `-e prod` for short value options

Use `--` to stop option parsing. Tokens after `--` are positional arguments even when they start with `-`.

## Defaults

Optional arguments use their configured default. Missing flags default to `false` unless a different default was configured. Missing value options default to their configured default or `null`. Missing repeatable value options default to an empty array.

## Output Streams

```php
use CommonPHP\Console\ConsoleOutput;

$output = new ConsoleOutput();

$output->write('Processing');
$output->writeln(' done');
$output->errorln('Warning: skipped one row');
$output->newLine();
```

`write()` and `writeln()` write to stdout. `error()` and `errorln()` write to stderr.

## Buffered Output

Use `ConsoleOutput::buffered()` in tests.

```php
$output = ConsoleOutput::buffered();

$output->writeln('OK');

$output->fetch();      // "OK\n"
$output->fetchError(); // ""
```

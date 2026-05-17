# Testing And QA

CommonPHP Console includes a package-local PHPUnit configuration and unit tests.

## Install Dependencies

From the package directory:

```bash
composer install
```

From the monorepo, the root `vendor` directory can also satisfy the test suite because `tests/bootstrap.php` checks both package and workspace autoloaders.

## Run PHPUnit

From the monorepo root:

```bash
vendor/bin/phpunit -c package/console/phpunit.xml.dist
```

On Windows:

```bash
vendor\bin\phpunit.bat -c package\console\phpunit.xml.dist
```

From `package/console`:

```bash
../../vendor/bin/phpunit -c phpunit.xml.dist
```

## Current Test Coverage

The unit suite covers:

- `CommandArgument` factories, metadata, synopsis output, variadic behavior, and invalid names;
- `CommandOption` factories, metadata, labels, synopsis output, invalid names, invalid shortcuts, and repeatable constraints;
- `CommandDefinition` names, descriptions, aliases, argument and option registration, usage output, duplicate detection, ordering rules, and shortcut lookup;
- `CommandRegistry` registration, callable commands, aliases, lookup, removal, iteration, counting, and duplicate collision errors;
- `CommandResult` success, failure, enum and integer normalization, existing-result passthrough, and invalid exit codes;
- `ConsoleInput` argv parsing, server argv fallback, defaults, required values, long options, short options, stacked flags, negated flags, repeatable options, `--` handling, help detection, and invalid input paths;
- `ConsoleOutput` stdout, stderr, buffering, newlines, and invalid stream handling;
- `CommandRunner` missing commands, unknown commands, default input/output creation, result normalization, exception wrapping, and console exception passthrough;
- `ConsoleExecutive` default list/help behavior, command execution, output rendering, error rendering, exit-code mapping, custom list/help overrides, and runner-provided registries;
- `ConsoleApplication` container definitions, command registration, callable registration, and registry sharing;
- `AbstractCommand`, `ExitCode`, and all exception factory helpers.

## Manual Review Areas

Manual review should still cover application-specific command UX, long-running process behavior, shell script integration, and commands whose dependencies touch filesystems, networks, databases, or third-party services.

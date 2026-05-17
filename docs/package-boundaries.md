# Package Boundaries

CommonPHP Console owns command-line command definitions, argument parsing, output helpers, command registration, command execution, and runtime executive integration.

## Belongs Here

- CLI command definitions and command contracts.
- Argument and option metadata.
- Parsing argv into bound input.
- Stdout and stderr output helpers.
- Command registries and runners.
- Console-specific exceptions and exit codes.
- Runtime integration for command-line applications.

## Does Not Belong Here

- HTTP request parsing, routing, middleware, or response emission.
- Database connections, query builders, or migrations.
- Authentication, authorization, sessions, or CSRF protection.
- Template rendering, UI components, or browser output.
- Scheduling, queues, workers, or long-running process supervision.
- Application-specific command discovery from folders or annotations.

Those concerns should live in their own packages and register commands with Console at the boundary.

## Integration Shape

Application packages should build command classes or callables and register them with `CommandRegistry` or `ConsoleApplication`. Commands should receive their dependencies through constructors when running inside Runtime, or close over dependencies when registered as callables in small scripts.

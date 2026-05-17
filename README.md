# CommonPHP Console

CommonPHP Console provides command-line execution support for CommonPHP applications. It defines the console executive, command structure, input/output handling, and terminal-oriented runtime integration.

The package is intended for CLI tools, maintenance commands, diagnostics, workers, and developer utilities that run through the CommonPHP runtime model.

## Requirements

- PHP `^8.5`
- `comphp/runtime:^0.3`

## Installation

Once this package is available through your Composer repositories, install it with:

```bash
composer require comphp/console
```

## Usage

```php
<?php

// TODO: Write usage
```

## Package Notes

This package should provide the console runtime mode, command discovery/registration, argument handling, output helpers, and command execution. Application bootstrapping remains in `comphp/runtime`.

## Error Handling

Command failures should return non-zero exit statuses and should use package-specific exceptions for invalid command definitions, invalid arguments, and execution failures.

## Documentation

- [Documentation index](docs/index.md)
- [Usage](docs/usage.md)
- [Testing](TESTING.md)
- [Contributing](CONTRIBUTING.md)
- [Security](SECURITY.md)

## License

MIT. See [LICENSE.md](LICENSE.md).

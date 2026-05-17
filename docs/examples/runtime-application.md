# Example: Runtime Application

Use `ConsoleApplication` when commands should run inside CommonPHP Runtime.

Related pages:

- [Runtime integration](../runtime-integration.md)
- [Commands](../commands.md)

```php
<?php

declare(strict_types=1);

use CommonPHP\Console\ConsoleApplication;
use CommonPHP\Runtime\Support\InitializationContext;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = new ConsoleApplication(new InitializationContext(root: dirname(__DIR__)));

$app->callable('status', static function ($input, $output): int {
    $output->writeln('Application is ready.');

    return 0;
});

exit($app->execute());
```

Run it:

```bash
php bin/console.php status
```

`ConsoleApplication` can also receive service providers and modules from Runtime before execution. Register commands during application setup, then let Runtime build the execution container.

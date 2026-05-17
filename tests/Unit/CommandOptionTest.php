<?php

declare(strict_types=1);

namespace CommonPHP\Console\Tests\Unit;

use CommonPHP\Console\CommandOption;
use CommonPHP\Console\Exceptions\InvalidCommandException;
use PHPUnit\Framework\TestCase;

final class CommandOptionTest extends TestCase
{
    public function testItCreatesFlags(): void
    {
        $option = CommandOption::flag('verbose', 'v', 'Show detail', true);

        self::assertSame('verbose', $option->name());
        self::assertSame('v', $option->shortcut());
        self::assertSame('Show detail', $option->description());
        self::assertFalse($option->acceptsValue());
        self::assertFalse($option->isRequired());
        self::assertTrue($option->default());
        self::assertFalse($option->isRepeatable());
        self::assertSame('[--verbose]', $option->synopsis());
        self::assertSame('-v, --verbose', $option->label());
    }

    public function testItCreatesValueOptions(): void
    {
        $option = CommandOption::value('tag', 't', 'Build tag', true, 'latest', true);

        self::assertSame('tag', $option->name());
        self::assertSame('t', $option->shortcut());
        self::assertSame('Build tag', $option->description());
        self::assertTrue($option->acceptsValue());
        self::assertTrue($option->isRequired());
        self::assertSame('latest', $option->default());
        self::assertTrue($option->isRepeatable());
        self::assertSame('--tag=VALUE', $option->synopsis());
        self::assertSame('-t, --tag=VALUE', $option->label());
    }

    public function testItRejectsInvalidNames(): void
    {
        $this->expectException(InvalidCommandException::class);

        new CommandOption('bad name');
    }

    public function testItRejectsInvalidShortcuts(): void
    {
        $this->expectException(InvalidCommandException::class);

        new CommandOption('verbose', 'vv');
    }

    public function testOnlyValueOptionsCanBeRepeatable(): void
    {
        $this->expectException(InvalidCommandException::class);

        new CommandOption('verbose', repeatable: true);
    }
}

<?php

declare(strict_types=1);

namespace CommonPHP\Console\Tests\Unit;

use CommonPHP\Console\CommandArgument;
use CommonPHP\Console\Exceptions\InvalidCommandException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CommandArgumentTest extends TestCase
{
    public function testItCreatesRequiredArguments(): void
    {
        $argument = CommandArgument::required('name', 'The user name');

        self::assertSame('name', $argument->name());
        self::assertSame('The user name', $argument->description());
        self::assertTrue($argument->isRequired());
        self::assertNull($argument->default());
        self::assertFalse($argument->isVariadic());
        self::assertSame('<name>', $argument->synopsis());
    }

    public function testItCreatesOptionalArguments(): void
    {
        $argument = CommandArgument::optional('environment', 'Target environment', 'dev');

        self::assertSame('environment', $argument->name());
        self::assertFalse($argument->isRequired());
        self::assertSame('dev', $argument->default());
        self::assertSame('[environment]', $argument->synopsis());
    }

    public function testItCreatesVariadicArguments(): void
    {
        $optional = CommandArgument::variadic('paths', 'Input paths');
        $required = CommandArgument::variadic('files', 'Input files', true);

        self::assertTrue($optional->isVariadic());
        self::assertFalse($optional->isRequired());
        self::assertSame([], $optional->default());
        self::assertSame('[paths...]', $optional->synopsis());
        self::assertSame('<files...>', $required->synopsis());
    }

    #[DataProvider('invalidNameProvider')]
    public function testItRejectsInvalidNames(string $name): void
    {
        $this->expectException(InvalidCommandException::class);

        new CommandArgument($name);
    }

    public function testVariadicDefaultsMustBeArrays(): void
    {
        $this->expectException(InvalidCommandException::class);

        new CommandArgument('paths', variadic: true, default: 'bad');
    }

    public static function invalidNameProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'starts with number' => ['1name'];
        yield 'contains colon' => ['bad:name'];
        yield 'contains space' => ['bad name'];
    }
}

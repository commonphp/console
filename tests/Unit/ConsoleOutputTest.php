<?php

declare(strict_types=1);

namespace CommonPHP\Console\Tests\Unit;

use CommonPHP\Console\ConsoleOutput;
use CommonPHP\Console\Exceptions\InvalidConsoleArgumentException;
use PHPUnit\Framework\TestCase;

final class ConsoleOutputTest extends TestCase
{
    public function testItWritesToOutputAndErrorStreams(): void
    {
        $output = ConsoleOutput::buffered();

        $output->write('one');
        $output->writeln(' two');
        $output->newLine(2);
        $output->error('bad');
        $output->errorln(' news');

        self::assertSame('one two' . PHP_EOL . PHP_EOL . PHP_EOL, $output->fetch());
        self::assertSame('bad news' . PHP_EOL, $output->fetchError());
    }

    public function testNewLineIgnoresNonPositiveCounts(): void
    {
        $output = ConsoleOutput::buffered();

        $output->newLine(0);
        $output->newLine(-1);

        self::assertSame('', $output->fetch());
    }

    public function testItRejectsInvalidOutputStreams(): void
    {
        $this->expectException(InvalidConsoleArgumentException::class);

        new ConsoleOutput('not-a-stream');
    }
}

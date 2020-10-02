<?php

declare(strict_types=1);

namespace PerformanceTimer\Tests;

use PerformanceTimer\Exception\TimerAlreadyRunningException;
use PerformanceTimer\Exception\TimerNotRunningException;
use PerformanceTimer\Exception\UnfinishedTimerException;
use PerformanceTimer\Timer;
use PHPUnit\Framework\TestCase;

class TimerTest extends TestCase
{
    public function testStartTimer(): void
    {
        $this->assertTrue(Timer::start('test-timer'));
    }

    public function testStartTimerTwiceThrowsAnException(): void
    {
        $this->expectException(TimerAlreadyRunningException::class);
        Timer::start('test-timer');
    }

    public function testDisabledExceptionDoesntThrowAnException(): void
    {
        Timer::initSettings(['throwExceptions' => false]);
        $this->assertFalse(Timer::start('test-timer'));
        Timer::initSettings(['throwExceptions' => true]);
    }

    public function testFinishTimer(): void
    {
        $this->assertTrue(Timer::end('test-timer'));
    }

    public function testFinishNotRunningTimerThrowsAnException(): void
    {
        $this->expectException(TimerNotRunningException::class);
        Timer::end('test-timer-2');
    }

    public function testMultipleTimers(): void
    {
        $this->assertTrue(Timer::start('test-timer'));
        $this->assertTrue(Timer::start('test-timer-2'));
    }

    public function testFetchResultWithUnfinishedTimersThrowsAnException()
    {
        $this->expectException(UnfinishedTimerException::class);
        Timer::handleResults();
    }

    public function testTimerResult(): void
    {
        $this->assertTrue(Timer::end('test-timer'));
        $this->assertTrue(Timer::end('test-timer-2'));

        $result = Timer::handleResults();
        $lines = explode(PHP_EOL, $result);

        $this->assertCount(3, $lines);
        $this->splitLines($lines);

        $this->assertSame('test-timer', $lines[0][0]);
        $this->assertSame('test-timer', $lines[1][0]);
        $this->assertSame('test-timer-2', $lines[2][0]);

        $this->assertIsNumeric($lines[0][1]);
        $this->assertIsNumeric($lines[1][1]);
        $this->assertIsNumeric($lines[2][1]);
    }

    public function testTimerResultIsEmptyAfterFetch(): void
    {
        $this->assertEmpty(Timer::handleResults());
    }

    protected function splitLines(array &$lines): void {
        $lines = array_map(function (string $line): array {
            return explode(',', $line);
        }, $lines);
    }
}

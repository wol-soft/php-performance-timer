<?php

declare(strict_types=1);

namespace PerformanceTimer\Tests;

use PerformanceTimer\Exception\UnfinishedTimerException;
use PerformanceTimer\Timer;
use PHPUnit\Framework\TestCase;

class TimerPluginTest extends TestCase
{
    public function testAddingPluginWithRunningTimersThrowsAnException()
    {
        $this->expectException(UnfinishedTimerException::class);
        Timer::start('test-timer');

        $this->addTimestampPlugin();
    }

    public function testAddPlugin(): void
    {
        Timer::end('test-timer');
        Timer::handleResults();

        $this->assertTrue($this->addTimestampPlugin());
        $this->assertTrue(Timer::addTimerPlugin(
            function () {
                return;
            },
            function () {
                return 'Hello';
            }
        ));
    }

    public function testPluginOutput(): void
    {
        Timer::start('plugin-timer');
        usleep(100);
        Timer::end('plugin-timer');

        [$key, $duration, $startTime, $endTime, $constValue] = explode(',', $result = Timer::handleResults());

        $this->assertSame('plugin-timer', $key);
        $this->assertIsNumeric($duration);
        $this->assertIsNumeric($startTime);
        $this->assertIsNumeric($endTime);
        $this->assertSame('Hello', $constValue);

        $this->assertGreaterThan((float) $startTime, (float) $endTime);
    }

    protected function addTimestampPlugin(): bool
    {
        return Timer::addTimerPlugin(
            function () {
                return microtime(true);
            },
            function ($startTime) {
                return [$startTime, microtime(true)];
            }
        );
    }
}

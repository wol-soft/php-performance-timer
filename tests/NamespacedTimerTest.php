<?php

declare(strict_types=1);

namespace PerformanceTimer\Tests;

use PerformanceTimer\Timer;
use PHPUnit\Framework\TestCase;

class NamespacedTimerTest extends TestCase
{
    public function testTimerOutOfNamespaceIsSkipped(): void
    {
        Timer::initSettings(['profileNamespace' => 'test.namespace']);

        $this->assertFalse(Timer::start('test-timer', 'production.namespace'));
        $this->assertFalse(Timer::end('test-timer', 'production.namespace'));
    }

    public function testStartTimerInNamespace(): void
    {
        $this->assertTrue(Timer::start('test-timer', 'test.namespace.layer3'));
        $this->assertTrue(Timer::start('test-timer-2'));
    }

    public function testEndTimerInNamespace(): void
    {
        $this->assertTrue(Timer::end('test-timer', 'test.namespace.layer3'));
        $this->assertTrue(Timer::end('test-timer-2'));
        Timer::handleResults();
    }
}

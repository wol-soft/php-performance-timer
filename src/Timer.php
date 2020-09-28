<?php

declare(strict_types=1);

namespace PerformanceTimer;

use Exception;
use PerformanceTimer\Exception\TimerAlreadyRunningException;
use PerformanceTimer\Exception\TimerNotRunningException;
use PerformanceTimer\Exception\UnfinishedTimerException;
use PerformanceTimer\OutputHandler\ToLogFileHandler;

class Timer
{
    private static $resultHandler;

    private static $runningTimers = [];
    private static $results = [];

    private static $settings = [
        'throwExceptions' => true,
        'outputHandler' => null,
        'profileNamespace' => '',
    ];

    public static function initSettings(array $settings): void {
        self::$settings = array_merge(self::$settings, $settings);
    }

    public static function start(string $timerKey, string $namespace = ''): void
    {
        if (!self::checkSkipTimer($namespace)) {
            return;
        }

        if (isset(self::$runningTimers[$timerKey])) {
            self::throw(new TimerAlreadyRunningException("Timer '$timerKey' already running"));
            return;
        }

        self::$runningTimers[$timerKey] = microtime(true);
    }

    public static function end(string $timerKey, string $namespace = ''): void
    {
        if (!self::checkSkipTimer($namespace)) {
            return;
        }

        if (!isset(self::$runningTimers[$timerKey])) {
            self::throw(new TimerNotRunningException("Timer '$timerKey' Not running"));
            return;
        }

        if (!isset(self::$results[$timerKey])) {
            self::$results[$timerKey] = [];
        }

        self::$results[$timerKey][] = microtime(true) - self::$runningTimers[$timerKey];

        unset(self::$runningTimers[$timerKey]);

        if (!self::$resultHandler) {
            self::$resultHandler = new TimerResultHandler();
        }
    }

    public static function handleResults()
    {
        if (empty(self::$results)) {
            return;
        }

        if (!empty(self::$runningTimers)) {
            self::throw(new UnfinishedTimerException(
                "Timer '" . implode("', '", array_keys(self::$runningTimers)) . "' not finished"
            ));
        }

        if (!self::$settings['outputHandler']) {
            self::$settings['outputHandler'] = new ToLogFileHandler();
        }

        self::$settings['outputHandler']->handle(self::$results);
        self::$results = [];
    }

    private static function checkSkipTimer(string $namespace): bool
    {
        return self::$settings['profileNamespace'] === false || (
            $namespace &&
            self::$settings['profileNamespace'] &&
            strpos($namespace, self::$settings['profileNamespace']) !== 0
        );
    }

    private static function throw(Exception $exception): void
    {
        if (self::$settings['throwExceptions']) {
            throw $exception;
        } else {
            error_log("PerformanceTimer error: " . $exception->getMessage());
        }
    }
}

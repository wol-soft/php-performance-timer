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

    private static $plugins = [];

    private static $settings = [
        'throwExceptions' => true,
        'outputHandler' => null,
        'profileNamespace' => '',
    ];

    public static function initSettings(array $settings): void
    {
        self::$settings = array_merge(self::$settings, $settings);
    }

    public static function addTimerPlugin(callable $start, callable $end): bool
    {
        if (!empty(self::$runningTimers)) {
            self::throw(new UnfinishedTimerException(
                "Adding timer plugin with unfinished timer '" . implode("', '", array_keys(self::$runningTimers)) . "'"
            ));

            return false;
        }

        self::$plugins[] = [$start, $end];

        return true;
    }

    public static function start(string $timerKey, string $namespace = ''): bool
    {
        if (self::checkSkipTimer($namespace)) {
            return false;
        }

        if (isset(self::$runningTimers[$timerKey])) {
            self::throw(new TimerAlreadyRunningException("Timer '$timerKey' already running"));
            return false;
        }

        self::$runningTimers[$timerKey] = [microtime(true)];

        foreach (self::$plugins as [$startCallback]) {
            self::$runningTimers[$timerKey][] = $startCallback();
        }

        return true;
    }

    public static function end(string $timerKey, string $namespace = ''): bool
    {
        if (self::checkSkipTimer($namespace)) {
            return false;
        }

        if (!isset(self::$runningTimers[$timerKey])) {
            self::throw(new TimerNotRunningException("Timer '$timerKey' Not running"));
            return false;
        }

        if (!isset(self::$results[$timerKey])) {
            self::$results[$timerKey] = [];
        }

        $result = [microtime(true) - self::$runningTimers[$timerKey][0]];

        foreach (self::$plugins as $index => [, $endCallback]) {
            array_push($result, ...(array)$endCallback(self::$runningTimers[$timerKey][$index]));
        }

        self::$results[$timerKey][] = $result;

        unset(self::$runningTimers[$timerKey]);

        if (!self::$resultHandler) {
            self::$resultHandler = new TimerResultHandler();
        }

        return true;
    }

    public static function handleResults()
    {
        if (empty(self::$results)) {
            return false;
        }

        if (!empty(self::$runningTimers)) {
            self::throw(new UnfinishedTimerException(
                "Timer '" . implode("', '", array_keys(self::$runningTimers)) . "' not finished"
            ));

            return false;
        }

        if (!self::$settings['outputHandler']) {
            self::$settings['outputHandler'] = new ToLogFileHandler();
        }

        $result = self::$settings['outputHandler']->handle(self::$results);
        self::$results = [];

        return $result;
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

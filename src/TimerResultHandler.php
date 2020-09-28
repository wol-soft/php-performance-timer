<?php

declare(strict_types=1);

namespace PerformanceTimer;

class TimerResultHandler
{
    public function __destruct()
    {
        Timer::handleResults();
    }
}

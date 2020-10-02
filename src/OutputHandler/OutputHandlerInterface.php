<?php

declare(strict_types=1);

namespace PerformanceTimer\OutputHandler;

interface OutputHandlerInterface
{
    public function handle(array $results);
}

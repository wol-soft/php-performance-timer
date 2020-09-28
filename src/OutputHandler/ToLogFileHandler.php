<?php

namespace PerformanceTimer\OutputHandler;

class ToLogFileHandler implements OutputHandlerInterface
{
    private $file;
    private $fileAmount;

    public function __construct(?string $file = null, int $fileAmount = 1)
    {
        $this->file = $file ?? sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'performance_timer.log';
        $this->fileAmount = $fileAmount;
    }

    public function handle(array $results): void
    {
        if ($this->fileAmount > 1) {
            $this->file .= rand(1, $this->fileAmount);
        }

        $output = '';
        foreach ($results as $timerKey => $timerResults) {
            $output .= array_reduce(
                $timerResults,
                static function (string $carry, float $time) use ($timerKey) {
                    return $carry . $timerKey . ',' . number_format(1000 * $time, 4) . PHP_EOL;
                },
                ''
            );
        }

        file_put_contents($this->file, $output, FILE_APPEND);
    }
}

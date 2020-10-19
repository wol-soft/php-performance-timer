<?php

declare(strict_types=1);

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

    public function handle(array $results)
    {
        if ($this->fileAmount > 1) {
            $this->file .= rand(1, $this->fileAmount);
        }

        $output = '';
        foreach ($results as $timerKey => $timerResults) {
            $output .= array_reduce(
                $timerResults,
                static function (string $carry, array $result) use ($timerKey) {
                    return $carry . join(
                        ',',
                        array_merge([$timerKey, number_format(1000 * array_shift($result), 4)], $result)
                    ) . PHP_EOL;
                },
                ''
            );
        }

        file_put_contents($this->file, trim($output) . PHP_EOL, FILE_APPEND);

        return trim($output);
    }
}

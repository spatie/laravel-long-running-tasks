<?php

namespace Spatie\LongRunningTasks\Strategies;

use Spatie\LongRunningTasks\Models\LongRunningTaskLogItem;

class ExponentialBackoffCheckStrategy implements CheckStrategy
{
    public function checkFrequencyInSeconds(LongRunningTaskLogItem $logItem): int
    {
        if ($logItem->attempt === 1) {
            return $logItem->check_frequency_in_seconds;
        }

        return ($logItem->check_frequency_in_seconds * 0.5) ** min($logItem->attempt, 4);
    }
}

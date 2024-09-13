<?php

namespace Spatie\LongRunningTasks\Strategies;

use Spatie\LongRunningTasks\Models\LongRunningTaskLogItem;

class LinearBackoffCheckStrategy implements CheckStrategy
{
    public function checkFrequencyInSeconds(LongRunningTaskLogItem $logItem): int
    {
        return $logItem->check_frequency_in_seconds * min([$logItem->attempt, 6]);
    }
}

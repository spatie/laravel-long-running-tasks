<?php

namespace Spatie\LongRunningTasks\Strategies;

use Spatie\LongRunningTasks\Models\LongRunningTaskLogItem;

class StandardBackoffCheckStrategy implements CheckStrategy
{
    public function frequencies(LongRunningTaskLogItem $logItem): array
    {
        $frequency = $logItem->check_frequency_in_seconds;

        return [
            $frequency,
            $frequency * 6,
            $frequency * 12,
            $frequency * 30,
            $frequency * 60,
        ];

    }

    public function checkFrequencyInSeconds(LongRunningTaskLogItem $logItem): int
    {
        $frequencies = $this->frequencies($logItem);

        return $logItem->attempt >= count($frequencies)
            ? $frequencies[count($frequencies) - 1]
            : $frequencies[($logItem->attempt % count($frequencies)) - 1];
    }

}

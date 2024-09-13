<?php

namespace Spatie\LongRunningTasks\Strategies;

use Spatie\LongRunningTasks\Models\LongRunningTaskLogItem;

interface CheckStrategy
{
    public function checkFrequencyInSeconds(LongRunningTaskLogItem $logItem): int;
}

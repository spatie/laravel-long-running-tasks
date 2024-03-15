<?php

namespace Spatie\LongRunningTasks\Tests\TestSupport\LongRunningTasks;

use Spatie\LongRunningTasks\Enums\TaskResult;
use Spatie\LongRunningTasks\LongRunningTask;
use Spatie\LongRunningTasks\Models\LongRunningTaskLogItem;

class LongRunningTestTask extends LongRunningTask
{
    public function check(LongRunningTaskLogItem $logItem): TaskResult
    {
        return TaskResult::StopChecking;
    }
}

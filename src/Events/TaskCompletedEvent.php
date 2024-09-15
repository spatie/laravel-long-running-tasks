<?php

namespace Spatie\LongRunningTasks\Events;

use Spatie\LongRunningTasks\Models\LongRunningTaskLogItem;

class TaskCompletedEvent
{
    public function __construct(public LongRunningTaskLogItem $longRunningTaskLogItem) {}
}

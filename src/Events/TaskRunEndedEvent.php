<?php

namespace Spatie\LongRunningTasks\Events;

use Spatie\LongRunningTasks\Models\LongRunningTaskLogItem;

class TaskRunEndedEvent
{
    public function __construct(public LongRunningTaskLogItem $longRunningTaskLogItem) {}
}

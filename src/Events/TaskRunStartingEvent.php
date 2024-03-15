<?php

namespace Spatie\LongRunningTasks\Events;

use Spatie\LongRunningTasks\Models\LongRunningTaskLogItem;

class TaskRunStartingEvent
{
    public function __construct(public LongRunningTaskLogItem $longRunningTaskLogItem)
    {

    }
}

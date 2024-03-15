<?php

namespace Spatie\LongRunningTasks\Events;

use Spatie\LongRunningTasks\Models\LongRunningTaskLogItem;

class TaskDidNotCompleteEvent
{
    public function __construct(public LongRunningTaskLogItem $longRunningTaskLogItem)
    {

    }
}

<?php

namespace Spatie\LongRunningTasks\Events;

use Spatie\LongRunningTasks\Models\LongRunningTaskLogItem;

class DispatchingNewRunEvent
{
    public function __construct(public LongRunningTaskLogItem $longRunningTaskLogItem) {}
}

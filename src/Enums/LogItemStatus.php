<?php

namespace Spatie\LongRunningTasks\Enums;

enum LogItemStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Failed = 'failed';
    case Completed = 'completed';
    case DidNotComplete = 'didNotComplete';
}

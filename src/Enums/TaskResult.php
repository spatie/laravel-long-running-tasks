<?php

namespace Spatie\LongRunningTasks\Enums;

enum TaskResult: string
{
    case ContinueChecking = 'continueChecking';
    case StopChecking = 'stopChecking';
}

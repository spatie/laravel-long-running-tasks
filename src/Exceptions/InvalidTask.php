<?php

namespace Spatie\LongRunningTasks\Exceptions;

use Exception;
use Spatie\LongRunningTasks\LongRunningTask;

class InvalidTask extends Exception
{
    public static function classDoesNotExist(string $class): self
    {
        return new static("The task class `{$class}` does not exist.");
    }

    public static function classIsNotATask(string $class): self
    {
        $baseClass = LongRunningTask::class;

        return new static("The task class `{$class}` does not extend the `{$baseClass}` base class.");
    }
}

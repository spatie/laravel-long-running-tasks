<?php

namespace Spatie\LongRunningTasks\Exceptions;

use Exception;
use Spatie\LongRunningTasks\Jobs\RunLongRunningTaskJob;

class InvalidJob extends Exception
{
    public static function make(string $class): self
    {
        $baseJobClass = RunLongRunningTaskJob::class;

        return new static("The job class `{$class}` does not extend the `{$baseJobClass}` base job class.");
    }
}

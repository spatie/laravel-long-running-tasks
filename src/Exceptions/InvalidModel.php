<?php

namespace Spatie\LongRunningTasks\Exceptions;

use Exception;
use Spatie\LongRunningTasks\Models\LongRunningTaskLogItem;

class InvalidModel extends Exception
{
    public static function make(string $class): self
    {
        $baseModelClass = LongRunningTaskLogItem::class;

        return new static("The model `{$class}` does not extend the `{$baseModelClass}` base model class.");
    }
}

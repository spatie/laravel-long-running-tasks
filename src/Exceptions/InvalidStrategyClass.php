<?php

namespace Spatie\LongRunningTasks\Exceptions;

use Exception;

class InvalidStrategyClass extends Exception
{
    public static function classDoesNotExist(string $class): self
    {
        return new static("Class `{$class}` does not exist.");
    }

    public static function classIsNotAStrategy(string $class): self
    {
        return new static("Class `{$class}` is not a strategy.");
    }
}

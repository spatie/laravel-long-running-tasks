<?php

namespace Spatie\LongRunningTasks;

use Carbon\Carbon;
use Exception;
use Spatie\LongRunningTasks\Enums\LogItemStatus;
use Spatie\LongRunningTasks\Enums\TaskResult;
use Spatie\LongRunningTasks\Exceptions\InvalidStrategyClass;
use Spatie\LongRunningTasks\Models\LongRunningTaskLogItem;
use Spatie\LongRunningTasks\Strategies\CheckStrategy;
use Spatie\LongRunningTasks\Strategies\DefaultCheckStrategy;
use Spatie\LongRunningTasks\Support\Config;

abstract class LongRunningTask
{
    protected array $meta = [];

    abstract public function check(LongRunningTaskLogItem $logItem): TaskResult;

    public function onFail(LongRunningTaskLogItem $logItem, Exception $exception): ?TaskResult
    {
        return TaskResult::StopChecking;
    }

    public static function make()
    {
        return new static();
    }

    public function meta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    public function queue(string $queue): self
    {
        $this->queue = $queue;

        return $this;
    }

    /**
     * @param class-string<CheckStrategy> $checkStrategy
     * @return $this
     */
    public function checkStrategy(string $checkStrategy): self
    {
        $this->checkStrategy = $checkStrategy;

        return $this;
    }

    public function checkFrequencyInSeconds(int $seconds): self
    {
        $this->checkFrequencyInSeconds = $seconds;

        return $this;
    }

    public function keepCheckingForInSeconds(int $seconds)
    {
        $this->keepCheckingForInSeconds = $seconds;

        return $this;
    }

    public function start(?array $meta = null): LongRunningTaskLogItem
    {
        if ($meta) {
            $this->meta($meta);
        }

        $logModel = Config::getLongRunningTaskLogItemModelClass();

        /** @var LongRunningTaskLogItem $logItem */
        $logItem = $logModel::create([
            'status' => LogItemStatus::Pending,
            'queue' => $this->getQueue(),
            'meta' => $this->meta,
            'type' => $this->type(),
            'check_frequency_in_seconds' => $this->getCheckFrequencyInSeconds(),
            'attempt' => 1,
            'stop_checking_at' => $this->stopCheckingAt(),
        ]);

        $logItem->dispatchJob();

        return $logItem;
    }

    protected function type(): string
    {
        return static::class;
    }

    protected function getCheckFrequencyInSeconds(): int
    {
        if (isset($this->checkFrequencyInSeconds)) {
            return $this->checkFrequencyInSeconds;
        }

        return config('long-running-tasks.default_check_frequency_in_seconds');
    }

    public function getQueue(): string
    {
        if (isset($this->queue)) {
            return $this->queue;
        }

        return config('long-running-tasks.queue');
    }

    public function getCheckStrategy(): CheckStrategy
    {
        $strategyClass = config('long-running-tasks.default_check_strategy_class', DefaultCheckStrategy::class);

        if (isset($this->checkStrategy)) {
            $strategyClass = $this->checkStrategy;
        }

        if (! class_exists($strategyClass)) {
            throw InvalidStrategyClass::classDoesNotExist($strategyClass);
        }

        if (! class_implements($strategyClass, CheckStrategy::class)) {
            throw InvalidStrategyClass::classIsNotAStrategy($strategyClass);
        }

        return app()->make($strategyClass);
    }

    public function stopCheckingAt(): Carbon
    {
        $timespan = config('long-running-tasks.keep_checking_for_in_seconds');

        if (isset($this->keepCheckingForInSeconds)) {
            $timespan = $this->keepCheckingForInSeconds;
        }

        return now()->addSeconds($timespan);
    }
}

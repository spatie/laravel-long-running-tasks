<?php

namespace Spatie\LongRunningTasks\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\LongRunningTasks\Enums\LogItemStatus;
use Spatie\LongRunningTasks\Enums\TaskResult;
use Spatie\LongRunningTasks\Events\DispatchingNewRunEvent;
use Spatie\LongRunningTasks\Events\TaskCompletedEvent;
use Spatie\LongRunningTasks\Events\TaskDidNotCompleteEvent;
use Spatie\LongRunningTasks\Events\TaskRunEndedEvent;
use Spatie\LongRunningTasks\Events\TaskRunStartingEvent;
use Spatie\LongRunningTasks\LongRunningTask;
use Spatie\LongRunningTasks\Models\LongRunningTaskLogItem;

class RunLongRunningTaskJob implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public LongRunningTaskLogItem $longRunningTaskLogItem)
    {

    }

    public function handle()
    {
        $task = $this->longRunningTaskLogItem->task();

        $this->longRunningTaskLogItem->markAsRunning();

        try {
            event(new TaskRunStartingEvent($this->longRunningTaskLogItem));

            $checkResult = $task->check($this->longRunningTaskLogItem);

            event(new TaskRunEndedEvent($this->longRunningTaskLogItem));
        } catch (Exception $exception) {
            $this->handleException($task, $exception);

            return;
        }

        $this->handleTaskResult($checkResult);
    }

    protected function handleTaskResult(TaskResult $checkResult): void
    {
        if ($checkResult === TaskResult::StopChecking) {
            $this->longRunningTaskLogItem->markAsCheckedEnded(LogItemStatus::Completed);

            event(new TaskCompletedEvent($this->longRunningTaskLogItem));

            return;
        }

        if (! $this->longRunningTaskLogItem->shouldKeepChecking()) {
            $this->longRunningTaskLogItem->markAsCheckedEnded(LogItemStatus::DidNotComplete);

            event(new TaskDidNotCompleteEvent($this->longRunningTaskLogItem));

            return;
        }

        $this->dispatchAgain();
    }

    protected function handleException(LongRunningTask $task, Exception $exception): void
    {
        $checkResult = $task->onFail($this->longRunningTaskLogItem, $exception);

        $checkResult ??= TaskResult::StopChecking;

        $this->longRunningTaskLogItem->update([
            'latest_exception' => [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ],
        ]);

        $this->longRunningTaskLogItem->markAsCheckedEnded(LogItemStatus::Failed);

        if ($checkResult == TaskResult::ContinueChecking) {
            $this->dispatchAgain();
        }
    }

    protected function dispatchAgain(): void
    {
        $this->longRunningTaskLogItem->markAsPending();

        event(new DispatchingNewRunEvent($this->longRunningTaskLogItem));

        $job = new static($this->longRunningTaskLogItem);

        $task = $this->longRunningTaskLogItem->task();
        $delay = $task->getCheckStrategy()->checkFrequencyInSeconds($this->longRunningTaskLogItem);

        $queue = $this->longRunningTaskLogItem->queue;

        dispatch($job)
            ->onQueue($queue)
            ->delay($delay);
    }

    public function uniqueId(): string|int
    {
        return $this->longRunningTaskLogItem->id;
    }
}

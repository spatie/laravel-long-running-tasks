<?php

use Illuminate\Support\Facades\Queue;
use Spatie\LongRunningTasks\Enums\LogItemStatus;
use Spatie\LongRunningTasks\Enums\TaskResult;
use Spatie\LongRunningTasks\Jobs\RunLongRunningTaskJob;
use Spatie\LongRunningTasks\LongRunningTask;
use Spatie\LongRunningTasks\Models\LongRunningTaskLogItem;
use Spatie\LongRunningTasks\Tests\TestSupport\LongRunningTasks\LongRunningTestTask;
use Spatie\TestTime\TestTime;

it('can create create a pending task', function () {
    Queue::fake();

    LongRunningTestTask::make()->start();

    expect(LongRunningTaskLogItem::all())->toHaveCount(1);

    expect(LongRunningTaskLogItem::first())
        ->type->toBe(LongRunningTestTask::class)
        ->status->toBe(LogItemStatus::Pending)
        ->attempt->toBe(1)
        ->check_frequency_in_seconds->toBe(10)
        ->meta->toBe([]);

    Queue::assertPushed(RunLongRunningTaskJob::class);
});

it('can handle a pending task that will complete', function () {
    $task = new class extends LongRunningTask
    {
        public function check(LongRunningTaskLogItem $logItem): TaskResult
        {
            return TaskResult::StopChecking;
        }
    };

    $task->start();

    expect(LongRunningTaskLogItem::first())
        ->status->toBe(LogItemStatus::Completed)
        ->attempt->toBe(1)
        ->run_count->toBe(1);
});

it('can handle a pending task that needs a couple of runs to complete', function () {
    $task = new class extends LongRunningTask
    {
        public function check(LongRunningTaskLogItem $logItem): TaskResult
        {
            return $logItem->run_count < 5
                ? TaskResult::ContinueChecking
                : TaskResult::StopChecking;
        }
    };

    $task->start();

    expect(LongRunningTaskLogItem::first())
        ->run_count->toBe(5)
        ->status->toBe(LogItemStatus::Completed)
        ->last_check_started_at->not()->toBeNull()
        ->last_check_ended_at->not()->toBeNull();
});

it('will can handle a task that always fails', function () {
    $task = new class extends LongRunningTask
    {
        public function check(LongRunningTaskLogItem $logItem): TaskResult
        {
            throw new Exception();
        }
    };

    $task->start();

    expect(LongRunningTaskLogItem::first())
        ->status->toBe(LogitemStatus::Failed)
        ->run_count->toBe(1)
        ->latest_exception->toHaveKeys(['message', 'trace']);
});

it('can handle a task that will recover', function () {
    $task = new class extends LongRunningTask
    {
        public function check(LongRunningTaskLogItem $logItem): TaskResult
        {
            if ($logItem->run_count < 3) {
                throw new Exception();
            }

            return TaskResult::StopChecking;
        }

        public function onFail(LongRunningTaskLogItem $logItem, Exception $exception): ?TaskResult
        {
            return TaskResult::ContinueChecking;
        }
    };

    $task->start();

    expect(LongRunningTaskLogItem::first())
        ->status->toBe(LogitemStatus::Completed)
        ->run_count->toBe(3)
        ->latest_exception->toBeNull();
});

it('will stop a task that would run forever', function () {
    config()->set('long-running-tasks.keep_checking_for_in_seconds', 1);

    $task = new class extends LongRunningTask
    {
        public function check(LongRunningTaskLogItem $logItem): TaskResult
        {
            return TaskResult::ContinueChecking;
        }
    };

    $task->start();

    expect(LongRunningTaskLogItem::first())
        ->status->toBe(LogitemStatus::DidNotComplete)
        ->run_count->toBeGreaterThan(1);
});

it('can add meta data', function () {
    $meta = ['foo' => 'bar'];

    LongRunningTestTask::make()->meta($meta)->start();

    expect(LongRunningTaskLogItem::first())
        ->meta->toBe($meta);
});

it('accepts meta data via the start method', function () {
    $meta = ['foo' => 'bar'];

    LongRunningTestTask::make()->start($meta);

    expect(LongRunningTaskLogItem::first())
        ->meta->toBe($meta);
});

it('will respect the custom check frequency on a task class', function () {
    Queue::fake();

    $task = new class extends LongRunningTask
    {
        public int $checkFrequencyInSeconds = 100;

        public function check(LongRunningTaskLogItem $logItem): TaskResult
        {
            return TaskResult::ContinueChecking;
        }
    };

    $task->start();

    expect(LongRunningTaskLogItem::first())
        ->check_frequency_in_seconds->toBe(100);
});

it('can use a custom check frequency when starting a task', function () {
    LongRunningTestTask::make()->checkFrequencyInSeconds(100)->start();

    expect(LongRunningTaskLogItem::first())
        ->check_frequency_in_seconds->toBe(100);
});

it('will respect the custom queue on a task class', function () {
    Queue::fake();

    $task = new class extends LongRunningTask
    {
        public string $queue = 'custom-queue';

        public function check(LongRunningTaskLogItem $logItem): TaskResult
        {
            return TaskResult::ContinueChecking;
        }
    };

    $task->start();

    expect(LongRunningTaskLogItem::first())->queue->toBe('custom-queue');
});

it('can use a custom queue when starting a task', function () {
    LongRunningTestTask::make()->queue('custom-queue')->start();

    expect(LongRunningTaskLogItem::first())
        ->queue->toBe('custom-queue');
});

it('will respect the keep checking for setting on a task class', function () {
    TestTime::freeze('Y-m-d H:i:s', '2024-01-01 00:00:00');

    Queue::fake();

    $task = new class extends LongRunningTask
    {
        public int $keepCheckingForInSeconds = 25;

        public function check(LongRunningTaskLogItem $logItem): TaskResult
        {
            return TaskResult::ContinueChecking;
        }
    };

    $task->start();

    $stopCheckingAt = LongRunningTaskLogItem::first()->stop_checking_at;

    expect($stopCheckingAt->format('Y-m-d H:i:s'))->toBe('2024-01-01 00:00:25');
});

it('can use the keep checking for value when starting a task', function () {
    TestTime::freeze('Y-m-d H:i:s', '2024-01-01 00:00:00');

    LongRunningTestTask::make()->keepCheckingForInSeconds(25)->start();

    $stopCheckingAt = LongRunningTaskLogItem::first()->stop_checking_at;

    expect($stopCheckingAt->format('Y-m-d H:i:s'))->toBe('2024-01-01 00:00:25');
});

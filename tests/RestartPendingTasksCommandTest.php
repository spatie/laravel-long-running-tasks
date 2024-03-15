<?php

use Spatie\LongRunningTasks\Commands\RestartPendingTasksCommand;
use Spatie\LongRunningTasks\Jobs\RunLongRunningTaskJob;
use Spatie\LongRunningTasks\Models\LongRunningTaskLogItem;
use Illuminate\Support\Facades\Queue;

it('can restart pending tasks', function () {
    Queue::fake();

    $logItem = LongRunningTaskLogItem::factory()->create();

    $this->artisan(RestartPendingTasksCommand::class)->assertSuccessful();

    Queue::assertPushed(
        RunLongRunningTaskJob::class,
        fn ($job) => $job->longRunningTaskLogItem->id === $logItem->id
    );
});

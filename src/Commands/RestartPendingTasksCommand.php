<?php

namespace Spatie\LongRunningTasks\Commands;

use Illuminate\Console\Command;
use Spatie\LongRunningTasks\Enums\LogItemStatus;
use Spatie\LongRunningTasks\Models\LongRunningTaskLogItem;
use Spatie\LongRunningTasks\Support\Config;

class RestartPendingTasksCommand extends Command
{
    public $signature = 'long-running-tasks:restart';

    public function handle(): int
    {
        $this->info('Starting long running tasks...');

        $logItems = Config::getLongRunningTaskLogItemModelClass();

        $logItems::query()
            ->where('status', LogItemStatus::Pending)
            ->each(function (LongRunningTaskLogItem $logItem) {
                $this->comment("Dispatching job for log item {$logItem->id}...");

                $logItem->dispatchJob();
            });

        $this->info('All done!');

        return self::SUCCESS;
    }
}

<?php

return [
    /*
     * Behind the scenes, this packages use a queue to call tasks.
     * Here you can choose the queue that should be used by default.
     */
    'queue' => 'default',

    /*
     * If a task determines that it should be continued, it will
     * be called again after this amount of time
     */
    'default_check_frequency_in_seconds' => 10,

    /*
     * When a task is not completed in this amount of time,
     * it will not run again, and marked as `didNotComplete`.
     */
    'keep_checking_for_in_seconds' => 60 * 5,

    /*
     * The model that will be used by default to track
     * the status of all tasks.
     */
    'log_model' => Spatie\LongRunningTasks\Models\LongRunningTaskLogItem::class,

    /*
     * The job responsible for calling tasks.
     */
    'task_job' => Spatie\LongRunningTasks\Jobs\RunLongRunningTaskJob::class,
];

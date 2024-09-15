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
     * The default class that implements a strategy for determining the check frequency in seconds.
     * - `DefaultCheckStrategy` will check the task every `LongRunningTaskLogItem::check_frequency_in_seconds` seconds.
     * - `StandardBackoffCheckStrategy` will check the task every `LongRunningTaskLogItem::check_frequency_in_seconds` seconds,
     *    but will increase the frequency with the multipliers 1, 6, 12, 30, 60, with the maximum being 60 times the original frequency.
     *    With the default check frequency, this translates to 10, 60, 120, 300, and 600 seconds between checks.
     * - `LinearBackoffCheckStrategy` will check the task every `LongRunningTaskLogItem::check_frequency_in_seconds` seconds,
     *   but will increase the frequency linearly with each attempt, up to a maximum multiple of 6 times the original frequency.
     * - `ExponentialBackoffCheckStrategy` will check the task every `LongRunningTaskLogItem::check_frequency_in_seconds` seconds,
     *   but will increase the frequency exponentially with each attempt, up to a maximum of 6 times the original frequency.
     */
    'default_check_strategy_class' => Spatie\LongRunningTasks\Strategies\DefaultCheckStrategy::class,

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

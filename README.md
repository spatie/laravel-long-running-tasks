# Handle long running tasks in a Laravel app

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-long-running-tasks.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-long-running-tasks)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/spatie/laravel-long-running-tasks/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/spatie/laravel-long-running-tasks/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/spatie/laravel-long-running-tasks/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/spatie/laravel-long-running-tasks/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-long-running-tasks.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-long-running-tasks)

Some services, like AWS Rekognition, allow you to start a task on their side. Instead of sending a webhook when the task is finished, the services expects you to regularly poll to know when it is finished (or get an updated status).

This package can help you monitor such long-running tasks that are executed externally.

You do so by creating a task like this.

```php
use Spatie\LongRunningTasks\LongRunningTask;
use Spatie\LongRunningTasks\Enums\TaskResult;
use Spatie\LongRunningTasks\LongRunningTask;

class MyTask extends LongRunningTask
{
    public function check(LongRunningTaskLogItem $logItem): TaskResult
    {
        // get some information about this task
        $meta = $logItem->meta
    
        // do some work here
        $allWorkIsDone = /* ... */
       
        // return wheter we should continue the task in a new run
        
         return $allWorkIsDone
            ? TaskResult::StopChecking
            : TaskResult::ContinueChecking
    }
}
```

When `TaskResult::ContinueChecking` is returned, this `check` function will be called again in 10 seconds (as defined in the `default_check_frequency_in_seconds` of the config file).

After you have created your task, you can start it like this.

```php
MyTask::make()->meta($anArray)->start();
```

The `check` method of `MyTask` will be called every 10 seconds until it returns `TaskResult::StopChecking`

## Installation

You can install the package via composer:

```bash
composer require spatie/laravel-long-running-tasks
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="long-running-tasks-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="long-running-tasks-config"
```

This is the contents of the published config file:

```php
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
```

This package make use of queues to call tasks again after a certain amount of time. Make sure you've set up [queues](https://laravel.com/docs/10.x/queues) in your Laravel app.

## Usage

To monitor a long-running task on an external service, you should define a task class. It should extend the `Spatie\LongRunningTasks\LongRunningTask` provided by the package.

It's `check` function should perform the work you need it to do and return a `TaskResult`. When returning `TaskResult::StopChecking` the task will not be called again. When returning `TaskResult::ContinueChecking` it will be called again in 10 seconds by default.

```php
use Spatie\LongRunningTasks\LongRunningTask;
use Spatie\LongRunningTasks\Enums\TaskResult;

class MyTask extends LongRunningTask
{
    public function check(LongRunningTaskLogItem $logItem): TaskResult
    {
        // get some information about this task
        $meta = $logItem->meta // returns an array
    
        // do some work here
        $allWorkIsDone = /* ... */
       
        // return wheter we should continue the task in a new run
        
         return $allWorkIsDone
            ? TaskResult::StopChecking
            : TaskResult::ContinueChecking
    }
}
```

To start the task above, you can call the `start` method.

```php
MyTask::make()->start();
```

This will create a record in the `long_running_task_log_items` table that tracks the progress of this task. The `check` method of `MyTask` will be called every 10 seconds until it returns `TaskResult::StopChecking`.

### Adding meta data

In most cases, you'll want to give a task some specific data it can act upon. This can be done by passing an array to the `meta` method.

```php
MyTask::make()->meta($arrayWithMetaData)->start();
```

Alternatively, you could also pass it to the `start` method.

```php
MyTask::make()->start($arrayWithMetaData);
```

The given array will be available on the `LongRunningTaskLogItem` instance that is passed to the `check` method of your task.

```php
class MyTask extends LongRunningTask
{
    public function check(LongRunningTaskLogItem $logItem): TaskResult
    {
        // get some information about this task
        $meta = $logItem->meta // returns an array

        // rest of method
    }
}
```

### Customizing the check interval

By default, when the `check` method of your task returns `TaskResult::ContinueChecking`, it will be called again in 10 seconds. You can customize that timespan by changing the value of the `default_check_frequency_in_seconds` key in the `long-running-tasks` config file.

You can also specify a check interval on your task itself.

```php
class MyTask extends LongRunningTask
{
    public int $checkFrequencyInSeconds = 20;
}
```

To specify a checking interval on a specific instance of a task, you can use the `checkInterval` method.

```php
MyTask::make()
   ->checkFrequencyInSeconds(30)
   ->start();
```

### Using a different queue

This package uses queues to call tasks again after a certain amount of time. By default, it will use the `default` queue. You can customize the queue that should be used by changing the value of the `queue` key in the `long-running-tasks` config file.

You can also specify a queue on your task itself.

```php
class MyTask extends LongRunningTask
{
    public string $queue = 'my-custom-queue';
}
```

To specify a queue on a specific instance of a task, you can use the `onQueue` method.

```php
MyTask::make()
   ->queue('my-custom-queue')
   ->start();
```

### Tracking the status of tasks

For each task that is started, a record will be created in the `long_running_task_log_items` table. This record will track the status of the task.

The `LongRunningTaskLogItem` model has a `status` attribute that can have the following values:

- `pending`: The task has not been started yet.
- `running`: The task is currently running.
- `completed`: The task has completed.
- `failed`: The task has failed. Probably an unhanded exception was thrown.
- `didNotComplete`: The task did not complete in the given amount of time.

The table also contains these properties:

- `task`: The fully qualified class name of the task.
- `queue`: The queue the task is running on.
- `check_frequency_in_seconds`: The amount of time in seconds that should pass before the task is checked again.
- `meta`: An array of meta data that was passed to the task.
- `last_check_started_at`: The date and time the task was started.
- `last_check_ended_at`: The date and time the task was ended.
- `stop_checking_at`: The date and time the task should stop being checked.
- `lastest_exception`: An array with keys `message` and `trace` that contains the latest exception that was thrown.
- `run_count`: The amount of times the task has been run.
- `attempt`: The amount of times the task has been attempted after a failure occurred.
- `created_at`: The date and time the record was created.

### Preventing never-ending tasks

The package has a way of preventing task to run indefinitely.

When a task is not completed in the amount of time specified in the `keep_checking_for_in_seconds` key of the `long-running-tasks` config file, it will not run again, and marked as `didNotComplete`. 

You can customize that timespan on a specific task.

```php
class MyTask extends LongRunningTask
{
    public int $keepCheckingForInSeconds = 60 * 10;
}
```

You can also specify the timespan on a specific instance of a task.

```php
MyTask::make()
   ->keepCheckingForInSeconds(60 * 10)
   ->start();
```

### Handling exceptions

When an exception is thrown in the `check` method of your task, it will be caught and stored in the `latest_exception` attribute of the `LongRunningTaskLogItem` model.

Optionally, you can define an `onFailure` method on your task. This method will be called when an exception is thrown in the `check` method.

```php
use Spatie\LongRunningTasks\LongRunningTask;
use Spatie\LongRunningTasks\Enums\TaskResult;

class MyTask extends LongRunningTask
{
    public function check(LongRunningTaskLogItem $logItem): TaskResult
    {
        throw new Exception('Something went wrong');
    }

    public function onFail(LongRunningTaskLogItem $logItem, Exception $exception): ?TaskResult
    {
        // handle the exception
    }
}
```

You can let the `onFail` method return a `TaskResult`. When it returns `TaskResult::ContinueChecking`, the task will be called again. If it doesn't return anything, the task will not be called again.

### Events

### Using your own model

If you need extra fields or functionality on the `LongRunningTaskLogItem` model, you can create your own model that extends the `LongRunningTaskLogItem` model provided by this package.

```php
namespace App\Models;

use Spatie\LongRunningTasks\Models\LongRunningTaskLogItem as BaseLongRunningTaskLogItem;

class LongRunningTaskLogItem extends BaseLongRunningTaskLogItem
{
    // your custom functionality
}
```

You should then update the `log_model` key in the `long-running-tasks` config file to point to your custom model.

```php
// in config/long-running-tasks.php

return [
    // ...

    'log_model' => App\Models\LongRunningTaskLogItem::class,
];
```

To fill the extra custom fields of your model, you could use the `creating` and `updating` events. You could use the `meta` property to pass data to the model.

```php
namespace App\Models;

use Spatie\LongRunningTasks\Models\LongRunningTaskLogItem as BaseLongRunningTaskLogItem;

class LongRunningTaskLogItem extends BaseLongRunningTaskLogItem
{
    protected static function booted()
    {
        static::creating(function ($logItem) {
            $customValue = $logItem->meta['some_key'];
            
            // optionally, you could unset the custom value from the meta array
            unset($logItem->meta['some_key']);
        
            $logItem->custom_field = $customValue;
        });
    }
}
```

### Using your own job

By default, the package uses the `RunLongRunningTaskJob` job to call tasks. If you want to use your own job, you can create a job that extends the `RunLongRunningTaskJob` job provided by this package.

```php
namespace App\Jobs;

use Spatie\LongRunningTasks\Jobs\RunLongRunningTaskJob as BaseRunLongRunningTaskJob;

class RunLongRunningTaskJob extends BaseRunLongRunningTaskJob
{
    // your custom functionality
}
```

You should then update the `task_job` key in the `long-running-tasks` config file to point to your custom job.

```php
// in config/long-running-tasks.php

return [
    // ...

    'task_job' => App\Jobs\RunLongRunningTaskJob::class,
];
```

### Events

The package fires events that you can listen to in your application to perform additional actions when certain events occur.

All of these events have a property `$longRunningTaskLogItem` that contains a `LongRunningTaskLogItem` model.

#### `Spatie\LongRunningTasks\Events\TaskRunStarting`

This event will be fired when a task is about to be run.

#### `Spatie\LongRunningTasks\Events\TaskRunEnded`

This event will be fired when a task has ended.

#### `Spatie\LongRunningTasks\Events\TaskCompleted`

This event will be fired when a task has completed.

#### `Spatie\LongRunningTasks\Events\TaskRunFailed`

This event will be fired when a task has failed.

#### `Spatie\LongRunningTasks\Events\TaskRunDidNotComplete`

This event will be fired when a task did not complete in the given amount of time.

#### `Spatie\LongRunningTasks\Events\DispatchingNewRunEvent`

This event will be fired when a new run of a task is about to be dispatched.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

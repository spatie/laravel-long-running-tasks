<?php

namespace Spatie\LongRunningTasks\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\LongRunningTasks\Enums\LogItemStatus;
use Spatie\LongRunningTasks\Models\LongRunningTaskLogItem;

class LongRunningTaskLogItemFactory extends Factory
{
    protected $model = LongRunningTaskLogItem::class;

    public function definition()
    {
        return [
            'type' => $this->faker->word,
            'queue' => 'default',
            'status' => LogItemStatus::Pending,
            'check_frequency_in_seconds' => 10,
            'meta' => [],
            'stop_checking_at' => now()->addSeconds(config('long-running-tasks.keep_checking_for_in_seconds')),
        ];
    }
}

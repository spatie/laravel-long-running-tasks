<?php

use Spatie\LongRunningTasks\Models\LongRunningTaskLogItem;
use Spatie\LongRunningTasks\Strategies\ExponentialBackoffCheckStrategy;
use Spatie\LongRunningTasks\Strategies\LinearBackoffCheckStrategy;

it('implements a linear backoff strategy', function () {
    $strategy = new LinearBackoffCheckStrategy;

    $logItem = LongRunningTaskLogItem::factory()->create();

    $logItem->check_frequency_in_seconds = 10;
    $logItem->attempt = 1;

    expect($strategy->checkFrequencyInSeconds($logItem))->toBe(10);

    $logItem->attempt = 2;
    expect($strategy->checkFrequencyInSeconds($logItem))->toBe(20);

    $logItem->attempt = 3;
    expect($strategy->checkFrequencyInSeconds($logItem))->toBe(30);

    $logItem->attempt = 4;
    expect($strategy->checkFrequencyInSeconds($logItem))->toBe(40);

    $logItem->attempt = 5;
    expect($strategy->checkFrequencyInSeconds($logItem))->toBe(50);

    $logItem->attempt = 6;
    expect($strategy->checkFrequencyInSeconds($logItem))->toBe(60);

    $logItem->attempt = 7;
    expect($strategy->checkFrequencyInSeconds($logItem))->toBe(60);
});

it('implements an exponential backoff strategy', function () {
    $strategy = new ExponentialBackoffCheckStrategy;

    $logItem = LongRunningTaskLogItem::factory()->create();

    $logItem->check_frequency_in_seconds = 10;
    $logItem->attempt = 1;

    expect($strategy->checkFrequencyInSeconds($logItem))->toBe(10);

    $logItem->attempt = 2;
    expect($strategy->checkFrequencyInSeconds($logItem))->toBe(25);

    $logItem->attempt = 3;
    expect($strategy->checkFrequencyInSeconds($logItem))->toBe(125);

    $logItem->attempt = 4;
    expect($strategy->checkFrequencyInSeconds($logItem))->toBe(625);

    $logItem->attempt = 5;
    expect($strategy->checkFrequencyInSeconds($logItem))->toBe(625);

    $logItem->attempt = 6;
    expect($strategy->checkFrequencyInSeconds($logItem))->toBe(625);
});

it('implements a standard backoff strategy', function () {
    $strategy = new Spatie\LongRunningTasks\Strategies\StandardBackoffCheckStrategy;

    $logItem = LongRunningTaskLogItem::factory()->create();

    $logItem->check_frequency_in_seconds = 10;
    $logItem->attempt = 1;

    expect($strategy->checkFrequencyInSeconds($logItem))->toBe(10);

    $logItem->attempt = 2;
    expect($strategy->checkFrequencyInSeconds($logItem))->toBe(60);

    $logItem->attempt = 3;
    expect($strategy->checkFrequencyInSeconds($logItem))->toBe(120);

    $logItem->attempt = 4;
    expect($strategy->checkFrequencyInSeconds($logItem))->toBe(300);

    $logItem->attempt = 5;
    expect($strategy->checkFrequencyInSeconds($logItem))->toBe(600);

    $logItem->attempt = 6;
    expect($strategy->checkFrequencyInSeconds($logItem))->toBe(600);
});

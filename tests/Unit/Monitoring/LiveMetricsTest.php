<?php

declare(strict_types=1);

use App\Domain\Monitoring\Services\LiveMetricsService;

describe('LiveMetricsService', function () {
    it('class exists', function () {
        expect(class_exists(LiveMetricsService::class))->toBeTrue();
    });

    it('has getMetrics method', function () {
        expect(method_exists(LiveMetricsService::class, 'getMetrics'))->toBeTrue();
    });

    it('has getDomainHealth method', function () {
        expect(method_exists(LiveMetricsService::class, 'getDomainHealth'))->toBeTrue();
    });

    it('has getEventThroughput method', function () {
        expect(method_exists(LiveMetricsService::class, 'getEventThroughput'))->toBeTrue();
    });

    it('has getStreamStatus method', function () {
        expect(method_exists(LiveMetricsService::class, 'getStreamStatus'))->toBeTrue();
    });

    it('has getSystemHealth method', function () {
        expect(method_exists(LiveMetricsService::class, 'getSystemHealth'))->toBeTrue();
    });

    it('has getProjectorLag method', function () {
        expect(method_exists(LiveMetricsService::class, 'getProjectorLag'))->toBeTrue();
    });
});

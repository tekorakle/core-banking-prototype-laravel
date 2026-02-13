<?php

declare(strict_types=1);

use App\Domain\Shared\EventSourcing\EventStreamPublisher;

describe('EventStreamPublisher', function () {
    it('class exists and is instantiable', function () {
        expect(class_exists(EventStreamPublisher::class))->toBeTrue();
    });

    it('has publish method', function () {
        expect(method_exists(EventStreamPublisher::class, 'publish'))->toBeTrue();
    });

    it('has publishBatch method', function () {
        expect(method_exists(EventStreamPublisher::class, 'publishBatch'))->toBeTrue();
    });

    it('has getStreamInfo method', function () {
        expect(method_exists(EventStreamPublisher::class, 'getStreamInfo'))->toBeTrue();
    });
});

<?php

declare(strict_types=1);

use App\Domain\Shared\EventSourcing\EventStreamConsumer;

describe('EventStreamConsumer', function () {
    it('class exists and is instantiable', function () {
        expect(class_exists(EventStreamConsumer::class))->toBeTrue();
    });

    it('has createConsumerGroup method', function () {
        expect(method_exists(EventStreamConsumer::class, 'createConsumerGroup'))->toBeTrue();
    });

    it('has consume method', function () {
        expect(method_exists(EventStreamConsumer::class, 'consume'))->toBeTrue();
    });

    it('has acknowledge method', function () {
        expect(method_exists(EventStreamConsumer::class, 'acknowledge'))->toBeTrue();
    });

    it('has getPending method', function () {
        expect(method_exists(EventStreamConsumer::class, 'getPending'))->toBeTrue();
    });

    it('has claimIdleMessages method', function () {
        expect(method_exists(EventStreamConsumer::class, 'claimIdleMessages'))->toBeTrue();
    });

    it('has getConsumerGroupInfo method', function () {
        expect(method_exists(EventStreamConsumer::class, 'getConsumerGroupInfo'))->toBeTrue();
    });
});

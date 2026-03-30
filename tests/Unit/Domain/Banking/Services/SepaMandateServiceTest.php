<?php

declare(strict_types=1);

use App\Domain\Banking\Models\SepaMandate;
use App\Domain\Banking\Services\SepaMandateService;
use Illuminate\Support\Collection;

uses(Tests\TestCase::class);

it('SepaMandateService class exists', function (): void {
    expect(class_exists(SepaMandateService::class))->toBeTrue();
});

it('SepaMandateService has createMandate method', function (): void {
    expect(method_exists(SepaMandateService::class, 'createMandate'))->toBeTrue();
});

it('SepaMandateService createMandate accepts (int, array) and returns SepaMandate', function (): void {
    $ref = new ReflectionMethod(SepaMandateService::class, 'createMandate');
    $params = $ref->getParameters();

    expect($params)->toHaveCount(2);
    expect($params[0]->getName())->toBe('userId');
    expect($params[0]->getType()?->getName())->toBe('int');
    expect($params[1]->getName())->toBe('data');
    expect($ref->getReturnType()?->getName())->toBe(SepaMandate::class);
});

it('SepaMandateService has suspendMandate method', function (): void {
    expect(method_exists(SepaMandateService::class, 'suspendMandate'))->toBeTrue();
});

it('SepaMandateService suspendMandate accepts (string) and returns SepaMandate', function (): void {
    $ref = new ReflectionMethod(SepaMandateService::class, 'suspendMandate');
    $params = $ref->getParameters();

    expect($params)->toHaveCount(1);
    expect($params[0]->getName())->toBe('mandateId');
    expect($params[0]->getType()?->getName())->toBe('string');
    expect($ref->getReturnType()?->getName())->toBe(SepaMandate::class);
});

it('SepaMandateService has cancelMandate method', function (): void {
    expect(method_exists(SepaMandateService::class, 'cancelMandate'))->toBeTrue();
});

it('SepaMandateService has reactivateMandate method', function (): void {
    expect(method_exists(SepaMandateService::class, 'reactivateMandate'))->toBeTrue();
});

it('SepaMandateService has getMandatesForUser method', function (): void {
    expect(method_exists(SepaMandateService::class, 'getMandatesForUser'))->toBeTrue();
});

it('SepaMandateService getMandatesForUser accepts int and returns Collection', function (): void {
    $ref = new ReflectionMethod(SepaMandateService::class, 'getMandatesForUser');
    $params = $ref->getParameters();

    expect($params)->toHaveCount(1);
    expect($params[0]->getName())->toBe('userId');
    expect($params[0]->getType()?->getName())->toBe('int');
    expect($ref->getReturnType()?->getName())->toBe(Collection::class);
});

it('SepaMandateService has findByMandateId method', function (): void {
    expect(method_exists(SepaMandateService::class, 'findByMandateId'))->toBeTrue();
});

it('SepaMandateService findByMandateId returns nullable SepaMandate', function (): void {
    $ref = new ReflectionMethod(SepaMandateService::class, 'findByMandateId');
    $returnType = $ref->getReturnType();

    expect($returnType)->not->toBeNull();
    expect($returnType?->allowsNull())->toBeTrue();
});

it('SepaMandateService has expireStaleMandate method', function (): void {
    expect(method_exists(SepaMandateService::class, 'expireStaleMandate'))->toBeTrue();
});

it('SepaMandateService expireStaleMandate returns int', function (): void {
    $ref = new ReflectionMethod(SepaMandateService::class, 'expireStaleMandate');
    expect($ref->getReturnType()?->getName())->toBe('int');
});

it('SepaMandateService can be instantiated', function (): void {
    $service = new SepaMandateService();
    expect($service)->toBeInstanceOf(SepaMandateService::class);
});

<?php

declare(strict_types=1);

describe('GraphQL Security Middleware', function () {
    it('instantiates GraphQLRateLimitMiddleware', function () {
        $limiter = new Illuminate\Cache\RateLimiter(
            new Illuminate\Cache\Repository(new Illuminate\Cache\ArrayStore())
        );
        $middleware = new App\Http\Middleware\GraphQLRateLimitMiddleware($limiter);
        expect($middleware)->toBeInstanceOf(App\Http\Middleware\GraphQLRateLimitMiddleware::class);
    });

    it('instantiates GraphQLQueryCostMiddleware', function () {
        $middleware = new App\Http\Middleware\GraphQLQueryCostMiddleware();
        expect($middleware)->toBeInstanceOf(App\Http\Middleware\GraphQLQueryCostMiddleware::class);
    });
});

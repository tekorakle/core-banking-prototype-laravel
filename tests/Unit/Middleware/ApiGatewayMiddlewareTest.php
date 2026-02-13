<?php

declare(strict_types=1);

use App\Http\Middleware\ApiGatewayMiddleware;

describe('ApiGatewayMiddleware', function () {
    it('can be instantiated', function () {
        $middleware = new ApiGatewayMiddleware();
        expect($middleware)->toBeInstanceOf(ApiGatewayMiddleware::class);
    });
});

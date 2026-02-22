<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\X402;

use App\Domain\X402\Models\X402MonetizedEndpoint;

class CreateX402MonetizedEndpointMutation
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args): X402MonetizedEndpoint
    {
        return X402MonetizedEndpoint::create([
            'route_method' => $args['route_method'],
            'route_path'   => $args['route_path'],
            'price_usd'    => $args['price_usd'],
            'network'      => $args['network'] ?? config('x402.server.default_network'),
            'description'  => $args['description'] ?? null,
            'is_active'    => $args['is_active'] ?? true,
        ]);
    }
}

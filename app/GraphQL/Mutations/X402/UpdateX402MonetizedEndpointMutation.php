<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\X402;

use App\Domain\X402\Models\X402MonetizedEndpoint;

class UpdateX402MonetizedEndpointMutation
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     */
    public function __invoke($_, array $args): X402MonetizedEndpoint
    {
        $endpoint = X402MonetizedEndpoint::findOrFail($args['id']);

        $updateData = array_filter([
            'price_usd'   => $args['price_usd'] ?? null,
            'network'     => $args['network'] ?? null,
            'description' => $args['description'] ?? null,
            'is_active'   => $args['is_active'] ?? null,
        ], fn ($v) => $v !== null);

        $endpoint->update($updateData);

        return $endpoint->fresh();
    }
}

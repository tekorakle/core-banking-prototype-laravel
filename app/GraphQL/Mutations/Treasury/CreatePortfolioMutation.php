<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Treasury;

use App\Domain\Treasury\Models\AssetAllocation;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CreatePortfolioMutation
{
    /**
     * @param  array<string, mixed>  $args
     */
    public function __invoke(mixed $rootValue, array $args): AssetAllocation
    {
        $user = Auth::user();

        if (! $user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        return AssetAllocation::create([
            'portfolio_id'   => Str::uuid()->toString(),
            'asset_class'    => $args['asset_class'],
            'target_weight'  => $args['target_weight'],
            'current_weight' => 0,
            'drift'          => 0,
            'target_amount'  => $args['target_amount'] ?? null,
            'current_amount' => 0,
        ]);
    }
}

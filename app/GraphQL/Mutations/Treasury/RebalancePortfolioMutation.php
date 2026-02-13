<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Treasury;

use App\Domain\Treasury\Models\AssetAllocation;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class RebalancePortfolioMutation
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

        $allocation = AssetAllocation::where('portfolio_id', $args['portfolio_id'])->first();

        if (! $allocation) {
            throw new ModelNotFoundException('Portfolio allocation not found.');
        }

        $threshold = $args['threshold'] ?? 5.0;
        $drift = (float) $allocation->drift;

        if (abs($drift) > $threshold) {
            $allocation->update([
                'current_weight' => $allocation->target_weight,
                'drift'          => 0,
                'current_amount' => $allocation->target_amount,
            ]);
        }

        return $allocation->fresh() ?? $allocation;
    }
}

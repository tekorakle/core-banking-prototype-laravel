<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\DeFi;

use App\Domain\DeFi\Models\DeFiPosition;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class ClosePositionMutation
{
    /**
     * @param  array<string, mixed>  $args
     */
    public function __invoke(mixed $rootValue, array $args): DeFiPosition
    {
        $user = Auth::user();

        if (! $user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        /** @var DeFiPosition|null $position */
        $position = DeFiPosition::query()->find($args['position_id']);

        if (! $position) {
            throw new ModelNotFoundException('DeFi position not found.');
        }

        $position->update([
            'status'    => 'closed',
            'closed_at' => now(),
        ]);

        return $position->fresh() ?? $position;
    }
}

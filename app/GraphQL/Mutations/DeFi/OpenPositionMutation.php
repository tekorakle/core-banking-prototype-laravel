<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\DeFi;

use App\Domain\DeFi\Models\DeFiPosition;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

class OpenPositionMutation
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

        return DeFiPosition::create([
            'user_id'   => $user->id,
            'protocol'  => $args['protocol'],
            'type'      => $args['type'],
            'chain'     => $args['chain'],
            'asset'     => $args['asset'],
            'amount'    => $args['amount'],
            'status'    => 'active',
            'opened_at' => now(),
        ]);
    }
}

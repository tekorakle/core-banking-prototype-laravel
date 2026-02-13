<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\CrossChain;

use App\Domain\CrossChain\Models\BridgeTransaction;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

class InitiateBridgeTransferMutation
{
    /**
     * @param  array<string, mixed>  $args
     */
    public function __invoke(mixed $rootValue, array $args): BridgeTransaction
    {
        $user = Auth::user();

        if (! $user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        return BridgeTransaction::create([
            'user_id'           => $user->id,
            'source_chain'      => $args['source_chain'],
            'dest_chain'        => $args['dest_chain'],
            'token'             => $args['token'],
            'amount'            => $args['amount'],
            'recipient_address' => $args['recipient_address'],
            'provider'          => $args['provider'] ?? 'wormhole',
            'status'            => 'pending',
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Wallet;

use App\Domain\Wallet\Models\MultiSigWallet;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TransferFundsMutation
{
    /**
     * @param  array<string, mixed>  $args
     */
    public function __invoke(mixed $rootValue, array $args): MultiSigWallet
    {
        $user = Auth::user();

        if (! $user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        /** @var MultiSigWallet|null $wallet */
        $wallet = MultiSigWallet::query()->find($args['from_wallet_id']);

        if (! $wallet) {
            throw new ModelNotFoundException('Wallet not found.');
        }

        Log::info('Transfer initiated via GraphQL', [
            'wallet_id'  => $wallet->id,
            'to_address' => $args['to_address'],
            'amount'     => $args['amount'],
            'chain'      => $args['chain'],
            'user_id'    => $user->id,
        ]);

        return $wallet;
    }
}

<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Wallet;

use App\Domain\Wallet\Models\MultiSigWallet;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;

class CreateWalletMutation
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

        return MultiSigWallet::create([
            'user_id'             => $user->id,
            'name'                => $args['name'],
            'chain'               => $args['chain'],
            'required_signatures' => $args['required_signatures'],
            'total_signers'       => $args['total_signers'],
            'status'              => 'active',
        ]);
    }
}

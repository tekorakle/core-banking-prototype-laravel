<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Account;

use App\Domain\Account\Models\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CreateAccountMutation
{
    /**
     * @param  array<string, mixed>  $args
     */
    public function __invoke(mixed $rootValue, array $args): Account
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return Account::create([
            'uuid'      => Str::uuid()->toString(),
            'name'      => $args['name'],
            'balance'   => 0,
            'frozen'    => false,
            'user_uuid' => $args['user_uuid'] ?? $user->uuid,
        ]);
    }
}

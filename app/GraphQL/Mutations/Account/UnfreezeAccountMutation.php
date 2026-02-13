<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Account;

use App\Domain\Account\Models\Account;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class UnfreezeAccountMutation
{
    /**
     * @param  array<string, mixed>  $args
     */
    public function __invoke(mixed $rootValue, array $args): Account
    {
        $user = Auth::user();

        if (! $user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        /** @var Account|null $account */
        $account = Account::query()->find($args['id']);

        if (! $account) {
            throw new ModelNotFoundException('Account not found.');
        }

        $account->update(['frozen' => false]);

        return $account->fresh() ?? $account;
    }
}

<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Payment;

use App\Domain\Payment\Models\PaymentTransaction;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InitiatePaymentMutation
{
    /**
     * @param  array<string, mixed>  $args
     */
    public function __invoke(mixed $rootValue, array $args): PaymentTransaction
    {
        $user = Auth::user();

        if (! $user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        return PaymentTransaction::create([
            'aggregate_uuid' => Str::uuid()->toString(),
            'account_uuid'   => $args['account_uuid'],
            'type'           => $args['type'],
            'status'         => 'pending',
            'amount'         => $args['amount'],
            'currency'       => $args['currency'],
            'payment_method' => $args['payment_method'] ?? null,
            'reference'      => $args['reference'] ?? Str::uuid()->toString(),
            'initiated_at'   => now(),
        ]);
    }
}

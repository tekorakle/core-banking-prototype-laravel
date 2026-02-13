<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\MobilePayment;

use App\Domain\MobilePayment\Models\PaymentIntent;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CreatePaymentIntentMutation
{
    /**
     * @param  array<string, mixed>  $args
     */
    public function __invoke(mixed $rootValue, array $args): PaymentIntent
    {
        $user = Auth::user();

        if (! $user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        return PaymentIntent::create([
            'public_id'              => 'pi_' . Str::random(24),
            'user_id'                => $user->id,
            'asset'                  => $args['asset'],
            'network'                => $args['network'],
            'amount'                 => $args['amount'],
            'merchant_id'            => $args['merchant_id'] ?? null,
            'shield_enabled'         => $args['shield_enabled'] ?? false,
            'status'                 => 'draft',
            'required_confirmations' => 1,
            'expires_at'             => now()->addHours(1),
        ]);
    }
}

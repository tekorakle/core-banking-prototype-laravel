<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Exchange;

use App\Domain\Exchange\Projections\Order;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PlaceOrderMutation
{
    /**
     * @param  array<string, mixed>  $args
     */
    public function __invoke(mixed $rootValue, array $args): Order
    {
        $user = Auth::user();

        if (! $user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        return Order::create([
            'order_id'       => Str::uuid()->toString(),
            'account_id'     => (string) $user->id,
            'type'           => $args['type'],
            'order_type'     => $args['order_type'],
            'base_currency'  => $args['base_currency'],
            'quote_currency' => $args['quote_currency'],
            'amount'         => $args['amount'],
            'price'          => $args['price'] ?? null,
            'status'         => 'open',
        ]);
    }
}

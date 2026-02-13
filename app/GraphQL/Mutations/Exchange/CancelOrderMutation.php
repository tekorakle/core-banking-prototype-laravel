<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Exchange;

use App\Domain\Exchange\Projections\Order;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class CancelOrderMutation
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

        /** @var Order|null $order */
        $order = Order::query()->where('order_id', $args['order_id'])->first();

        if (! $order) {
            throw new ModelNotFoundException('Order not found.');
        }

        $order->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return $order->fresh() ?? $order;
    }
}

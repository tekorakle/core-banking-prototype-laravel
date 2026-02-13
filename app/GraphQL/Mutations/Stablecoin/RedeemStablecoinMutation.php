<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\Stablecoin;

use App\Domain\Stablecoin\Models\StablecoinReserve;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class RedeemStablecoinMutation
{
    /**
     * @param  array<string, mixed>  $args
     */
    public function __invoke(mixed $rootValue, array $args): StablecoinReserve
    {
        $user = Auth::user();

        if (! $user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        $reserve = StablecoinReserve::where('reserve_id', $args['reserve_id'])->first();

        if (! $reserve) {
            throw new ModelNotFoundException('Stablecoin reserve not found.');
        }

        $currentAmount = (float) $reserve->amount;
        $redeemAmount = (float) $args['amount'];

        if ($redeemAmount > $currentAmount) {
            throw new InvalidArgumentException('Redeem amount exceeds available reserve.');
        }

        $newAmount = number_format($currentAmount - $redeemAmount, 18, '.', '');
        $reserve->update([
            'amount'    => $newAmount,
            'value_usd' => $newAmount,
        ]);

        return $reserve->fresh() ?? $reserve;
    }
}

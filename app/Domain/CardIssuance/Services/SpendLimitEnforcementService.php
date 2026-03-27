<?php

declare(strict_types=1);

namespace App\Domain\CardIssuance\Services;

use App\Domain\CardIssuance\Models\Card;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Enforces per-card spend limits using cache-backed running totals.
 *
 * Cards may have a spend_limit_cents with a spend_limit_interval (daily/monthly).
 * This service tracks cumulative spend per interval and rejects transactions
 * that would exceed the configured limit.
 */
class SpendLimitEnforcementService
{
    /**
     * Check if a spend amount is within the card's configured limit.
     *
     * @param  string $cardToken The issuer card token
     * @param  float  $amount    The transaction amount in decimal (e.g. 25.50)
     * @return bool   True if the spend is allowed, false if it exceeds the limit
     */
    public function checkLimit(string $cardToken, float $amount): bool
    {
        $card = Card::where('issuer_card_token', $cardToken)->first();

        if ($card === null || $card->spend_limit_cents === null) {
            return true;
        }

        $interval = $card->spend_limit_interval ?? 'daily';
        $limitCents = $card->spend_limit_cents;
        $amountCents = (int) round($amount * 100);

        $cacheKey = $this->buildCacheKey($cardToken, $interval);

        /** @var int $currentSpendCents */
        $currentSpendCents = Cache::get($cacheKey, 0);

        if (($currentSpendCents + $amountCents) > $limitCents) {
            Log::warning('SpendLimitEnforcement: Limit exceeded', [
                'card_token'      => $cardToken,
                'interval'        => $interval,
                'limit_cents'     => $limitCents,
                'current_spend'   => $currentSpendCents,
                'requested_cents' => $amountCents,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Record a successful spend against the card's running total.
     *
     * @param string $cardToken The issuer card token
     * @param float  $amount    The transaction amount in decimal (e.g. 25.50)
     */
    public function recordSpend(string $cardToken, float $amount): void
    {
        $card = Card::where('issuer_card_token', $cardToken)->first();

        if ($card === null || $card->spend_limit_cents === null) {
            return;
        }

        $interval = $card->spend_limit_interval ?? 'daily';
        $amountCents = (int) round($amount * 100);

        $cacheKey = $this->buildCacheKey($cardToken, $interval);
        $ttl = $this->getTtl($interval);

        /** @var int $currentSpendCents */
        $currentSpendCents = Cache::get($cacheKey, 0);

        Cache::put($cacheKey, $currentSpendCents + $amountCents, $ttl);

        Log::info('SpendLimitEnforcement: Spend recorded', [
            'card_token'   => $cardToken,
            'interval'     => $interval,
            'amount_cents' => $amountCents,
            'new_total'    => $currentSpendCents + $amountCents,
        ]);
    }

    /**
     * Build the cache key for a card's spend tracking.
     */
    private function buildCacheKey(string $cardToken, string $interval): string
    {
        $dateSuffix = match ($interval) {
            'monthly' => date('Y-m'),
            default   => date('Y-m-d'),
        };

        return "card_spend:{$interval}:{$cardToken}:{$dateSuffix}";
    }

    /**
     * Get the TTL in seconds for a given interval.
     */
    private function getTtl(string $interval): int
    {
        return match ($interval) {
            'monthly' => 2678400, // 31 days
            default   => 86400,   // 24 hours
        };
    }
}

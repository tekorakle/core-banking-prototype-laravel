<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\X402;

use App\Domain\X402\Models\X402Payment;

class X402PaymentStatsQuery
{
    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    public function __invoke($_, array $args): array
    {
        $period = $args['period'] ?? 'day';
        $since = match ($period) {
            'week'  => now()->subWeek(),
            'month' => now()->subMonth(),
            default => now()->subDay(),
        };

        $query = X402Payment::where('created_at', '>=', $since);

        return [
            'period'           => $period,
            'total_payments'   => (clone $query)->count(),
            'total_settled'    => (clone $query)->where('status', 'settled')->count(),
            'total_failed'     => (clone $query)->where('status', 'failed')->count(),
            'total_volume_usd' => (string) ((clone $query)->where('status', 'settled')->sum('amount') ?: '0'),
            'unique_payers'    => (clone $query)->whereNotNull('payer_address')->distinct('payer_address')->count(),
        ];
    }
}

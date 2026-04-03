<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Account\Models\BlockchainAddress;
use App\Domain\Account\Models\BlockchainTransaction;
use App\Domain\MobilePayment\Enums\ActivityItemType;
use App\Domain\MobilePayment\Models\ActivityFeedItem;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Backfill Solana transaction history from Helius API.
 *
 * Fetches historical transactions for all registered Solana addresses
 * using Helius Enhanced Transactions API and stores them in both
 * blockchain_address_transactions and activity_feed_items tables.
 */
class SolanaTransactionBackfillCommand extends Command
{
    protected $signature = 'solana:backfill-transactions
        {--address= : Backfill only this specific address}
        {--limit=50 : Maximum transactions per address}
        {--dry-run : Show count without storing}';

    protected $description = 'Backfill Solana transaction history from Helius Enhanced Transactions API';

    public function handle(): int
    {
        $apiKey = (string) config('services.helius.api_key');

        if ($apiKey === '') {
            $this->error('HELIUS_API_KEY is not set.');

            return self::FAILURE;
        }

        $specificAddress = $this->option('address');
        $limit = (int) $this->option('limit');

        $query = BlockchainAddress::where('chain', 'solana')->where('is_active', true);

        if ($specificAddress) {
            $query->where('address', $specificAddress);
        }

        $addresses = $query->get();

        if ($addresses->isEmpty()) {
            $this->warn('No Solana addresses found in blockchain_addresses table.');

            return self::FAILURE;
        }

        $this->info("Backfilling transactions for {$addresses->count()} Solana address(es)...");

        $totalCreated = 0;
        $totalSkipped = 0;

        foreach ($addresses as $blockchainAddress) {
            $user = User::where('uuid', $blockchainAddress->user_uuid)->first();

            if ($user === null) {
                $this->warn("  Skipping {$blockchainAddress->address} — no user found");

                continue;
            }

            $this->info("  Fetching history for {$blockchainAddress->address}...");

            $transactions = $this->fetchEnhancedTransactions($apiKey, $blockchainAddress->address, $limit);

            if ($transactions === null) {
                $this->warn("  Failed to fetch transactions for {$blockchainAddress->address}");

                continue;
            }

            if ($this->option('dry-run')) {
                $this->info("  Would store {$transactions->count()} transactions (dry-run)");
                $totalCreated += $transactions->count();

                continue;
            }

            foreach ($transactions as $tx) {
                $signature = $tx['signature'] ?? '';

                if ($signature === '') {
                    continue;
                }

                $isIncoming = $this->isIncoming($blockchainAddress->address, $tx);
                $amount = $this->resolveAmount($tx);
                $token = $this->resolveToken($tx);
                $fromAddr = $this->resolveFromAddress($tx);
                $toAddr = $this->resolveToAddress($tx);
                $timestamp = isset($tx['timestamp']) ? date('Y-m-d H:i:s', (int) $tx['timestamp']) : now()->toDateTimeString();

                $btx = BlockchainTransaction::firstOrCreate(
                    ['tx_hash' => $signature, 'chain' => 'solana'],
                    [
                        'address_uuid' => $blockchainAddress->uuid,
                        'type'         => $isIncoming ? 'receive' : 'send',
                        'amount'       => $amount,
                        'fee'          => bcadd(is_numeric($feeRaw = (string) ($tx['fee'] ?? 0)) ? $feeRaw : '0', '0', 18),
                        'from_address' => $fromAddr ?? '',
                        'to_address'   => $toAddr ?? '',
                        'status'       => 'confirmed',
                        'metadata'     => $tx,
                    ]
                );

                $feedItem = ActivityFeedItem::firstOrCreate(
                    ['reference_type' => 'solana_tx', 'reference_id' => $signature],
                    [
                        'user_id'       => $user->id,
                        'activity_type' => $isIncoming ? ActivityItemType::TRANSFER_IN : ActivityItemType::TRANSFER_OUT,
                        'amount'        => $isIncoming ? $amount : '-' . $amount,
                        'asset'         => $token,
                        'network'       => 'solana',
                        'status'        => 'confirmed',
                        'protected'     => false,
                        'from_address'  => $fromAddr,
                        'to_address'    => $toAddr,
                        'occurred_at'   => $timestamp,
                        'metadata'      => ['signature' => $signature],
                    ]
                );

                if ($btx->wasRecentlyCreated) {
                    $totalCreated++;
                } else {
                    $totalSkipped++;
                }
            }
        }

        if ($this->option('dry-run')) {
            $this->info("Dry run complete. Would store {$totalCreated} transactions.");
        } else {
            $this->info("Done. Created: {$totalCreated}, Already existed: {$totalSkipped}.");
        }

        return self::SUCCESS;
    }

    /**
     * Fetch enhanced transaction history from Helius API.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>|null
     */
    private function fetchEnhancedTransactions(string $apiKey, string $address, int $limit): ?\Illuminate\Support\Collection
    {
        $url = "https://api.helius.xyz/v0/addresses/{$address}/transactions";

        try {
            $response = Http::timeout(30)->get($url, [
                'api-key' => $apiKey,
                'limit'   => min($limit, 100),
            ]);

            if (! $response->successful()) {
                Log::warning('Helius: Failed to fetch transaction history', [
                    'address' => $address,
                    'status'  => $response->status(),
                ]);

                return null;
            }

            return collect($response->json());
        } catch (Throwable $e) {
            Log::error('Helius: Transaction history fetch error', [
                'address' => $address,
                'error'   => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param array<string, mixed> $tx
     */
    private function isIncoming(string $address, array $tx): bool
    {
        /** @var array<int, array<string, mixed>> $tokenTransfers */
        $tokenTransfers = $tx['tokenTransfers'] ?? [];

        foreach ($tokenTransfers as $transfer) {
            if (($transfer['toUserAccount'] ?? '') === $address) {
                return true;
            }
            if (($transfer['fromUserAccount'] ?? '') === $address) {
                return false;
            }
        }

        /** @var array<int, array<string, mixed>> $nativeTransfers */
        $nativeTransfers = $tx['nativeTransfers'] ?? [];

        foreach ($nativeTransfers as $transfer) {
            if (($transfer['toUserAccount'] ?? '') === $address) {
                return true;
            }
            if (($transfer['fromUserAccount'] ?? '') === $address) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $tx
     */
    private function resolveAmount(array $tx): string
    {
        /** @var array<int, array<string, mixed>> $tokenTransfers */
        $tokenTransfers = $tx['tokenTransfers'] ?? [];

        if (! empty($tokenTransfers)) {
            $raw = (string) ($tokenTransfers[0]['tokenAmount'] ?? '0');

            return bcadd(is_numeric($raw) ? $raw : '0', '0', 8);
        }

        /** @var array<int, array<string, mixed>> $nativeTransfers */
        $nativeTransfers = $tx['nativeTransfers'] ?? [];

        if (! empty($nativeTransfers)) {
            $lamports = (string) ($nativeTransfers[0]['amount'] ?? '0');

            return bcdiv(is_numeric($lamports) ? $lamports : '0', '1000000000', 9);
        }

        return '0';
    }

    /**
     * @param array<string, mixed> $tx
     */
    private function resolveToken(array $tx): string
    {
        /** @var array<int, array<string, mixed>> $tokenTransfers */
        $tokenTransfers = $tx['tokenTransfers'] ?? [];

        if (! empty($tokenTransfers)) {
            $mint = $tokenTransfers[0]['mint'] ?? '';

            return match ($mint) {
                'EPjFWdd5AufqSSqeM2qN1xzybapC8G4wEGGkZwyTDt1v' => 'USDC',
                'Es9vMFrzaCERmJfrF4H2FYD4KCoNkY11McCe8BenwNYB' => 'USDT',
                default                                        => 'SPL',
            };
        }

        return 'SOL';
    }

    /**
     * @param array<string, mixed> $tx
     */
    private function resolveFromAddress(array $tx): ?string
    {
        $transfers = $tx['tokenTransfers'] ?? $tx['nativeTransfers'] ?? [];

        return $transfers[0]['fromUserAccount'] ?? null;
    }

    /**
     * @param array<string, mixed> $tx
     */
    private function resolveToAddress(array $tx): ?string
    {
        $transfers = $tx['tokenTransfers'] ?? $tx['nativeTransfers'] ?? [];

        return $transfers[0]['toUserAccount'] ?? null;
    }
}

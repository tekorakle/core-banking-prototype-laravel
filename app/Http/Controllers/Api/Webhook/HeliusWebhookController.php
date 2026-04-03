<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Webhook;

use App\Domain\Account\Models\BlockchainAddress;
use App\Domain\Account\Models\BlockchainTransaction;
use App\Domain\MobilePayment\Enums\ActivityItemType;
use App\Domain\MobilePayment\Models\ActivityFeedItem;
use App\Domain\Wallet\Events\Broadcast\WalletBalanceUpdated;
use App\Domain\Wallet\Factories\BlockchainConnectorFactory;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Handle Helius Solana webhooks for address activity monitoring.
 *
 * Helius sends Enhanced Transaction webhooks for Solana token transfers.
 * We check if any involved address belongs to a user and broadcast
 * a balance update via WebSocket.
 *
 * Setup in Helius Dashboard (https://dev.helius.xyz/dashboard):
 *   1. Create webhook → Enhanced Transactions
 *   2. Webhook URL: https://zelta.app/api/webhooks/helius/solana
 *   3. Add authorization header with your HELIUS_WEBHOOK_SECRET
 *   4. Select token accounts to monitor (USDC: EPjFWdd5AufqSSqeM2qN1xzybapC8G4wEGGkZwyTDt1v)
 */
class HeliusWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        if (! $this->verifySecret($request)) {
            Log::warning('Helius webhook secret verification failed', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['error' => 'Invalid authorization'], 401);
        }

        /** @var array<int, array<string, mixed>> $transactions */
        $transactions = $request->input('0') !== null ? $request->all() : [$request->all()];

        $processed = 0;

        foreach ($transactions as $tx) {
            if (! is_array($tx)) {
                continue;
            }

            $processed += $this->processTransaction($tx);
        }

        Log::info('Helius webhook processed', [
            'transaction_count' => count($transactions),
            'matched'           => $processed,
        ]);

        return response()->json(['processed' => $processed]);
    }

    /**
     * Process a single Helius enhanced transaction.
     *
     * @param array<string, mixed> $tx
     */
    private function processTransaction(array $tx): int
    {
        $matched = 0;

        // Skip transactions with no token or native transfers
        if (empty($tx['tokenTransfers']) && empty($tx['nativeTransfers'])) {
            return 0;
        }

        // Check token transfers for known addresses
        /** @var array<int, array<string, mixed>> $tokenTransfers */
        $tokenTransfers = $tx['tokenTransfers'] ?? [];

        foreach ($tokenTransfers as $transfer) {
            $from = $transfer['fromUserAccount'] ?? '';
            $to = $transfer['toUserAccount'] ?? '';

            foreach ([$from, $to] as $address) {
                if (! is_string($address) || $address === '') {
                    continue;
                }

                if ($this->isKnownAddress($address)) {
                    $this->broadcastBalanceUpdate($address, $tx);
                    $matched++;
                }
            }
        }

        // Also check native SOL transfers
        /** @var array<int, array<string, mixed>> $nativeTransfers */
        $nativeTransfers = $tx['nativeTransfers'] ?? [];

        foreach ($nativeTransfers as $transfer) {
            $from = $transfer['fromUserAccount'] ?? '';
            $to = $transfer['toUserAccount'] ?? '';

            foreach ([$from, $to] as $address) {
                if (! is_string($address) || $address === '') {
                    continue;
                }

                if ($this->isKnownAddress($address)) {
                    $this->broadcastBalanceUpdate($address, $tx);
                    $matched++;
                }
            }
        }

        return $matched;
    }

    /**
     * Check if a Solana address belongs to a known user.
     */
    private function isKnownAddress(string $address): bool
    {
        $cacheKey = "solana_known_addr:{$address}";

        return Cache::remember($cacheKey, 300, function () use ($address): bool {
            return BlockchainAddress::where('address', $address)
                ->where('chain', 'solana')
                ->exists();
        });
    }

    /**
     * Broadcast a balance update and store transaction for a matched address.
     *
     * @param array<string, mixed> $tx
     */
    private function broadcastBalanceUpdate(string $address, array $tx): void
    {
        $blockchainAddress = BlockchainAddress::where('address', $address)
            ->where('chain', 'solana')
            ->first();

        if ($blockchainAddress === null) {
            return;
        }

        $user = \App\Models\User::where('uuid', $blockchainAddress->user_uuid)->first();

        if ($user === null) {
            return;
        }

        $signature = $tx['signature'] ?? '';
        $isIncoming = $this->isIncoming($address, $tx);
        $amount = $this->resolveAmount($tx);
        $token = $this->resolveToken($tx);
        $fromAddr = $this->resolveFromAddress($tx);
        $toAddr = $this->resolveToAddress($tx);

        // Store blockchain transaction record (deduped by tx_hash)
        if ($signature !== '') {
            BlockchainTransaction::firstOrCreate(
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

            // Store activity feed item (so it shows in mobile activity tab)
            // reference_id is UUID column — hash signature into deterministic UUID for dedup
            $refId = self::signatureToUuid($signature);
            ActivityFeedItem::firstOrCreate(
                ['reference_type' => 'solana_tx', 'reference_id' => $refId],
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
                    'occurred_at'   => now(),
                    'metadata'      => ['signature' => $signature],
                ]
            );
        }

        WalletBalanceUpdated::dispatch($user->id, 'solana');

        // Invalidate and pre-warm balance cache so next mobile request hits warm cache
        Cache::forget("solana_balance:{$address}");
        Cache::forget("solana_known_addr:{$address}");

        try {
            $connector = BlockchainConnectorFactory::create('solana');
            $balanceData = $connector->getBalance($address);
            Cache::put("solana_balance:{$address}", $balanceData->balance, 300);
        } catch (Throwable) {
            // Pre-warm is best-effort — next request will query fresh
        }

        Log::info('Helius: Solana transaction stored and balance update broadcast', [
            'address'   => $address,
            'user_id'   => $user->id,
            'tx_type'   => $isIncoming ? 'receive' : 'send',
            'token'     => $token,
            'amount'    => $amount,
            'signature' => $signature,
        ]);
    }

    /**
     * Determine if the transaction is incoming relative to the matched address.
     *
     * @param array<string, mixed> $tx
     */
    private function isIncoming(string $address, array $tx): bool
    {
        // Check token transfers first
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

        // Check native transfers
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

        return true; // Default to incoming if direction unclear
    }

    /**
     * Resolve the human-readable amount from a Helius transaction.
     *
     * @param array<string, mixed> $tx
     */
    private function resolveAmount(array $tx): string
    {
        /** @var array<int, array<string, mixed>> $tokenTransfers */
        $tokenTransfers = $tx['tokenTransfers'] ?? [];

        if (! empty($tokenTransfers)) {
            $raw = (string) ($tokenTransfers[0]['tokenAmount'] ?? '0');

            // Helius provides tokenAmount already in decimal form
            // Normalize to numeric-string for bcmath
            return bcadd(is_numeric($raw) ? $raw : '0', '0', 8);
        }

        /** @var array<int, array<string, mixed>> $nativeTransfers */
        $nativeTransfers = $tx['nativeTransfers'] ?? [];

        if (! empty($nativeTransfers)) {
            // Native SOL: amount is in lamports (1 SOL = 1e9 lamports)
            $lamports = (string) ($nativeTransfers[0]['amount'] ?? '0');

            return bcdiv($lamports, '1000000000', 9);
        }

        return '0';
    }

    /**
     * Resolve the token symbol from a Helius transaction.
     *
     * @param array<string, mixed> $tx
     */
    private function resolveToken(array $tx): string
    {
        /** @var array<int, array<string, mixed>> $tokenTransfers */
        $tokenTransfers = $tx['tokenTransfers'] ?? [];

        if (! empty($tokenTransfers)) {
            $mint = $tokenTransfers[0]['mint'] ?? '';

            // Known Solana token mints
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

    /**
     * Convert a Solana transaction signature to a deterministic UUID.
     *
     * The activity_feed_items.reference_id column is UUID type, but Solana
     * signatures are ~88 char base58 strings. Hash into UUID v5-like format.
     */
    public static function signatureToUuid(string $signature): string
    {
        $hash = md5("solana_tx:{$signature}");

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 12, 4),
            substr($hash, 16, 4),
            substr($hash, 20, 12)
        );
    }

    /**
     * Verify the webhook secret from the Authorization header.
     *
     * Helius sends the secret in the Authorization header.
     */
    private function verifySecret(Request $request): bool
    {
        $secret = (string) config('services.helius.webhook_secret', '');

        if ($secret === '') {
            if (app()->environment('production')) {
                Log::error('Helius: HELIUS_WEBHOOK_SECRET not set in production');

                return false;
            }

            return true;
        }

        // Helius sends the authHeader value exactly as configured in the dashboard
        $authHeader = $request->header('Authorization', '');

        if (! is_string($authHeader) || $authHeader === '') {
            return false;
        }

        return hash_equals($secret, $authHeader);
    }
}

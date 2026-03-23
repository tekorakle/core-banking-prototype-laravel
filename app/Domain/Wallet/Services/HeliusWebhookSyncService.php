<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Services;

use App\Domain\Account\Models\BlockchainAddress;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Syncs Solana user addresses with Helius webhook monitoring.
 *
 * Unlike Alchemy (which monitors token contract addresses catching all transfers),
 * Helius requires explicit account addresses. This service automatically adds/removes
 * user Solana addresses to the Helius webhook via their API.
 *
 * Helius API: PUT /v0/webhooks/{id} with full accountAddresses array.
 * Max 100,000 addresses per webhook. Costs 100 credits per API call.
 */
class HeliusWebhookSyncService
{
    private const CACHE_KEY = 'helius_webhook_addresses';

    private const CACHE_TTL = 3600; // 1 hour

    public function __construct()
    {
    }

    /**
     * Add a Solana address to the Helius webhook.
     */
    public function addAddress(string $address): bool
    {
        $webhookId = $this->getWebhookId();
        $apiKey = $this->getApiKey();

        if ($webhookId === '' || $apiKey === '') {
            Log::debug('Helius: Webhook sync skipped — not configured');

            return false;
        }

        $currentAddresses = $this->getCurrentAddresses($webhookId, $apiKey);

        if (in_array($address, $currentAddresses, true)) {
            return true; // Already registered
        }

        $currentAddresses[] = $address;

        return $this->updateWebhookAddresses($webhookId, $apiKey, $currentAddresses);
    }

    /**
     * Remove a Solana address from the Helius webhook.
     */
    public function removeAddress(string $address): bool
    {
        $webhookId = $this->getWebhookId();
        $apiKey = $this->getApiKey();

        if ($webhookId === '' || $apiKey === '') {
            return false;
        }

        $currentAddresses = $this->getCurrentAddresses($webhookId, $apiKey);
        $updated = array_values(array_diff($currentAddresses, [$address]));

        if (count($updated) === count($currentAddresses)) {
            return true; // Address wasn't in the list
        }

        return $this->updateWebhookAddresses($webhookId, $apiKey, $updated);
    }

    /**
     * Sync all Solana addresses from the database to Helius.
     *
     * Call this periodically or after bulk operations.
     */
    public function syncAllAddresses(): int
    {
        $webhookId = $this->getWebhookId();
        $apiKey = $this->getApiKey();

        if ($webhookId === '' || $apiKey === '') {
            Log::warning('Helius: Cannot sync — HELIUS_WEBHOOK_ID or HELIUS_API_KEY not set');

            return 0;
        }

        $addresses = BlockchainAddress::where('chain', 'solana')
            ->where('is_active', true)
            ->pluck('address')
            ->unique()
            ->values()
            ->all();

        $this->updateWebhookAddresses($webhookId, $apiKey, $addresses);

        Log::info('Helius: Synced all Solana addresses', ['count' => count($addresses)]);

        return count($addresses);
    }

    /**
     * Get the current webhook address list from Helius (cached).
     *
     * @return array<string>
     */
    private function getCurrentAddresses(string $webhookId, string $apiKey): array
    {
        /** @var array<string> $cached */
        $cached = Cache::get(self::CACHE_KEY);

        if (is_array($cached)) {
            return $cached;
        }

        $response = Http::timeout(15)
            ->get("https://api.helius.xyz/v0/webhooks/{$webhookId}", [
                'api-key' => $apiKey,
            ]);

        if (! $response->successful()) {
            Log::error('Helius: Failed to fetch webhook', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [];
        }

        /** @var array<string> $addresses */
        $addresses = $response->json('accountAddresses', []);

        Cache::put(self::CACHE_KEY, $addresses, self::CACHE_TTL);

        return $addresses;
    }

    /**
     * Update the webhook with a new address list.
     *
     * @param array<string> $addresses
     */
    private function updateWebhookAddresses(string $webhookId, string $apiKey, array $addresses): bool
    {
        $response = Http::timeout(15)
            ->put("https://api.helius.xyz/v0/webhooks/{$webhookId}?api-key={$apiKey}", [
                'accountAddresses' => array_values(array_unique($addresses)),
            ]);

        if (! $response->successful()) {
            Log::error('Helius: Failed to update webhook addresses', [
                'status' => $response->status(),
                'body'   => $response->body(),
                'count'  => count($addresses),
            ]);

            return false;
        }

        // Update cache
        Cache::put(self::CACHE_KEY, array_values(array_unique($addresses)), self::CACHE_TTL);

        Log::info('Helius: Webhook addresses updated', ['count' => count($addresses)]);

        return true;
    }

    private function getWebhookId(): string
    {
        return (string) config('services.helius.webhook_id', '');
    }

    private function getApiKey(): string
    {
        return (string) config('services.helius.api_key', '');
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Observers;

use App\Domain\Account\Models\BlockchainAddress;
use App\Domain\Wallet\Services\HeliusWebhookSyncService;

/**
 * Observes BlockchainAddress model events to sync Solana
 * addresses with external webhook providers (Helius).
 */
class BlockchainAddressObserver
{
    public function __construct(
        private readonly HeliusWebhookSyncService $heliusSync,
    ) {
    }

    public function created(BlockchainAddress $address): void
    {
        if ($address->chain === 'solana' && $address->is_active) {
            $this->heliusSync->addAddress($address->address);
        }
    }

    public function deleted(BlockchainAddress $address): void
    {
        if ($address->chain === 'solana') {
            $this->heliusSync->removeAddress($address->address);
        }
    }
}

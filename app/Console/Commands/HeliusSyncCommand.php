<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Wallet\Services\HeliusWebhookSyncService;
use Illuminate\Console\Command;

/**
 * Sync all Solana addresses to Helius webhook monitoring.
 *
 * Run periodically or after bulk imports to ensure all user
 * Solana addresses are registered with the Helius webhook.
 */
class HeliusSyncCommand extends Command
{
    protected $signature = 'helius:sync
        {--dry-run : Show count without updating Helius}';

    protected $description = 'Sync all Solana blockchain addresses to Helius webhook monitoring';

    public function handle(HeliusWebhookSyncService $service): int
    {
        if ($this->option('dry-run')) {
            $count = \App\Domain\Account\Models\BlockchainAddress::where('chain', 'solana')
                ->where('is_active', true)
                ->count();
            $this->info("Would sync {$count} Solana addresses to Helius webhook.");

            return self::SUCCESS;
        }

        $this->info('Syncing Solana addresses to Helius...');

        $count = $service->syncAllAddresses();

        if ($count === 0) {
            $this->warn('No addresses synced. Check HELIUS_API_KEY and HELIUS_WEBHOOK_ID are set.');

            return self::FAILURE;
        }

        $this->info("Synced {$count} Solana addresses to Helius webhook.");

        return self::SUCCESS;
    }
}

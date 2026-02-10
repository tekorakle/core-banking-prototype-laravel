<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\CrossChain\Contracts\AssetMapperInterface;
use App\Domain\CrossChain\Services\BridgeOrchestratorService;
use App\Domain\CrossChain\Services\BridgeTransactionTracker;
use App\Domain\CrossChain\Services\CrossChainAssetRegistryService;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for the CrossChain domain (cross-chain bridges & asset management).
 */
class CrossChainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/crosschain.php',
            'crosschain',
        );

        $this->app->singleton(CrossChainAssetRegistryService::class);

        $this->app->bind(AssetMapperInterface::class, CrossChainAssetRegistryService::class);

        $this->app->singleton(BridgeTransactionTracker::class);

        $this->app->singleton(BridgeOrchestratorService::class, function ($app) {
            return new BridgeOrchestratorService();
        });
    }

    public function boot(): void
    {
        //
    }
}

<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\CrossChain\Contracts\AssetMapperInterface;
use App\Domain\CrossChain\Services\Adapters\AxelarBridgeAdapter;
use App\Domain\CrossChain\Services\Adapters\DemoBridgeAdapter;
use App\Domain\CrossChain\Services\Adapters\LayerZeroBridgeAdapter;
use App\Domain\CrossChain\Services\Adapters\WormholeBridgeAdapter;
use App\Domain\CrossChain\Services\BridgeFeeComparisonService;
use App\Domain\CrossChain\Services\BridgeOrchestratorService;
use App\Domain\CrossChain\Services\BridgeTransactionTracker;
use App\Domain\CrossChain\Services\CrossChainAssetRegistryService;
use App\Domain\CrossChain\Services\CrossChainTokenMapService;
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
            $orchestrator = new BridgeOrchestratorService();

            // Always register demo adapter
            $orchestrator->registerAdapter(new DemoBridgeAdapter());

            // Register production adapters when enabled
            if (config('crosschain.wormhole.enabled', false)) {
                $orchestrator->registerAdapter(new WormholeBridgeAdapter());
            }

            if (config('crosschain.layerzero.enabled', false)) {
                $orchestrator->registerAdapter(new LayerZeroBridgeAdapter());
            }

            if (config('crosschain.axelar.enabled', false)) {
                $orchestrator->registerAdapter(new AxelarBridgeAdapter());
            }

            return $orchestrator;
        });

        $this->app->singleton(BridgeFeeComparisonService::class, function ($app) {
            return new BridgeFeeComparisonService(
                $app->make(BridgeOrchestratorService::class),
            );
        });

        $this->app->singleton(CrossChainTokenMapService::class, function ($app) {
            return new CrossChainTokenMapService(
                $app->make(CrossChainAssetRegistryService::class),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}

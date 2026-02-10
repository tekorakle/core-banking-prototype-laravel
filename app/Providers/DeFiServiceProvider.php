<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\DeFi\Services\DeFiPortfolioService;
use App\Domain\DeFi\Services\DeFiPositionTrackerService;
use App\Domain\DeFi\Services\SwapAggregatorService;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for the DeFi domain (protocol connectors & position tracking).
 */
class DeFiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/defi.php',
            'defi',
        );

        $this->app->singleton(SwapAggregatorService::class, function () {
            return new SwapAggregatorService();
        });

        $this->app->singleton(DeFiPositionTrackerService::class);

        $this->app->singleton(DeFiPortfolioService::class, function ($app) {
            return new DeFiPortfolioService(
                $app->make(DeFiPositionTrackerService::class),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}

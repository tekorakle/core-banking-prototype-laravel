<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Relayer\Contracts\BundlerInterface;
use App\Domain\Relayer\Contracts\PaymasterInterface;
use App\Domain\Relayer\Services\DemoBundlerService;
use App\Domain\Relayer\Services\DemoPaymasterService;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for the Relayer domain (ERC-4337 Gas Abstraction).
 *
 * Binds bundler and paymaster contracts to implementations.
 * In production, swap with real ERC-4337 bundler services like:
 * - Pimlico, Stackup, Alchemy Account Abstraction
 */
class RelayerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the bundler interface to the demo implementation
        // For production, create AlchemyBundlerService, PimlicoBundlerService, etc.
        $this->app->bind(BundlerInterface::class, function ($app) {
            return new DemoBundlerService();
        });

        // Bind the paymaster interface to the demo implementation
        // For production, integrate with your actual paymaster contract
        $this->app->bind(PaymasterInterface::class, function ($app) {
            return new DemoPaymasterService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

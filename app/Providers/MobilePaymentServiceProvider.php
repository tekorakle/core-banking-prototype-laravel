<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\MobilePayment\Contracts\MerchantLookupServiceInterface;
use App\Domain\MobilePayment\Contracts\PaymentIntentServiceInterface;
use App\Domain\MobilePayment\Services\DemoMerchantLookupService;
use App\Domain\MobilePayment\Services\PaymentIntentService;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for the MobilePayment domain.
 */
class MobilePaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            PaymentIntentServiceInterface::class,
            PaymentIntentService::class,
        );

        // In production, swap DemoMerchantLookupService for a real implementation
        $this->app->bind(
            MerchantLookupServiceInterface::class,
            DemoMerchantLookupService::class,
        );
    }

    public function boot(): void
    {
        //
    }
}

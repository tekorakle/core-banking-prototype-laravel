<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\RegTech\Adapters\ESMAAdapter;
use App\Domain\RegTech\Adapters\FCAAdapter;
use App\Domain\RegTech\Adapters\FinCENAdapter;
use App\Domain\RegTech\Adapters\MASAdapter;
use App\Domain\RegTech\Services\RegTechOrchestrationService;
use Illuminate\Support\ServiceProvider;

/**
 * Registers jurisdiction-specific regulatory filing adapters.
 */
class RegTechServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (! config('regtech.enabled', true)) {
            return;
        }

        $this->registerAdapters();
    }

    private function registerAdapters(): void
    {
        $orchestration = $this->app->make(RegTechOrchestrationService::class);

        $orchestration->registerAdapter('us', new FinCENAdapter());
        $orchestration->registerAdapter('eu', new ESMAAdapter());
        $orchestration->registerAdapter('uk', new FCAAdapter());
        $orchestration->registerAdapter('sg', new MASAdapter());
    }
}

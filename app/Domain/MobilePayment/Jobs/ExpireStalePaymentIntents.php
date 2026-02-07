<?php

declare(strict_types=1);

namespace App\Domain\MobilePayment\Jobs;

use App\Domain\MobilePayment\Enums\PaymentIntentStatus;
use App\Domain\MobilePayment\Models\PaymentIntent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Expire stale payment intents that haven't been submitted.
 *
 * Runs every minute to catch intents that nobody is polling.
 */
class ExpireStalePaymentIntents implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        $expired = PaymentIntent::expirable()->get();

        $count = 0;
        foreach ($expired as $intent) {
            $intent->transitionTo(PaymentIntentStatus::EXPIRED);
            $count++;
        }

        if ($count > 0) {
            Log::info("Expired {$count} stale payment intents.");
        }
    }
}

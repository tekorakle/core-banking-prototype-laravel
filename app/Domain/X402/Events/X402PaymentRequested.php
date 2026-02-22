<?php

declare(strict_types=1);

namespace App\Domain\X402\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class X402PaymentRequested
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly string $endpointMethod,
        public readonly string $endpointPath,
        public readonly string $network,
        public readonly string $amount,
        public readonly string $payTo,
    ) {
    }
}

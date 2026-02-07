<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\MobilePayment;

use App\Domain\MobilePayment\Services\NetworkAvailabilityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class NetworkStatusController extends Controller
{
    public function __construct(
        private readonly NetworkAvailabilityService $networkAvailabilityService,
    ) {
    }

    /**
     * Get the status of all supported payment networks.
     *
     * GET /v1/networks/status
     */
    public function __invoke(): JsonResponse
    {
        $statuses = $this->networkAvailabilityService->getNetworkStatuses();

        return response()->json([
            'success' => true,
            'data'    => [
                'networks' => $statuses,
            ],
        ]);
    }
}

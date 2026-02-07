<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\MobilePayment;

use App\Domain\MobilePayment\Services\TransactionDetailService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionDetailService $transactionDetailService,
    ) {
    }

    /**
     * Get transaction details.
     *
     * GET /v1/transactions/{txId}
     */
    public function show(Request $request, string $txId): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $details = $this->transactionDetailService->getDetails(
            $txId,
            $user->id,
        );

        if (! $details) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'TRANSACTION_NOT_FOUND',
                    'message' => 'Transaction not found.',
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $details,
        ]);
    }
}

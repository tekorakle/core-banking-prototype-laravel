<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Wallet;

use App\Domain\Wallet\Services\WalletTransferService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Wallet\ResolveNameRequest;
use App\Http\Requests\Wallet\ValidateAddressRequest;
use App\Http\Requests\Wallet\WalletQuoteRequest;
use Illuminate\Http\JsonResponse;
use Throwable;

class WalletTransferController extends Controller
{
    public function __construct(
        private readonly WalletTransferService $transferService,
    ) {
    }

    /**
     * Validate a blockchain address.
     *
     * GET /v1/wallet/validate-address?address=...&network=SOLANA
     */
    public function validateAddress(ValidateAddressRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->transferService->validateAddress(
            $validated['address'],
            $validated['network'],
        );

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }

    /**
     * Resolve an ENS/SNS name to a blockchain address.
     *
     * POST /v1/wallet/resolve-name
     */
    public function resolveName(ResolveNameRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->transferService->resolveName(
            $validated['name'],
            $validated['network'],
        );

        $statusCode = $result['resolved'] ? 200 : 422;

        return response()->json([
            'success' => $result['resolved'],
            'data'    => $result,
        ], $statusCode);
    }

    /**
     * Get a fee quote for a wallet-to-wallet transfer.
     *
     * POST /v1/wallet/quote
     */
    public function quote(WalletQuoteRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $result = $this->transferService->getTransferQuote(
                $validated['network'],
                $validated['asset'],
                $validated['amount'],
            );

            return response()->json([
                'success' => true,
                'data'    => $result,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'QUOTE_FAILED',
                    'message' => $e->getMessage(),
                ],
            ], 400);
        }
    }
}

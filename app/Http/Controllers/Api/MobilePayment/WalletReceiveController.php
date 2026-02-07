<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\MobilePayment;

use App\Domain\MobilePayment\Enums\PaymentAsset;
use App\Domain\MobilePayment\Enums\PaymentNetwork;
use App\Domain\MobilePayment\Services\ReceiveAddressService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletReceiveController extends Controller
{
    public function __construct(
        private readonly ReceiveAddressService $receiveAddressService,
    ) {
    }

    /**
     * Get a receive address for the authenticated user.
     *
     * GET /v1/wallet/receive?asset=USDC&network=SOLANA
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'asset'   => ['required', 'string', 'in:USDC'],
            'network' => ['required', 'string', 'in:SOLANA,TRON'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $network = PaymentNetwork::from($request->input('network'));
        $asset = PaymentAsset::from($request->input('asset'));

        $data = $this->receiveAddressService->getReceiveAddress(
            $user->id,
            $network,
            $asset,
        );

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}

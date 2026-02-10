<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Commerce;

use App\Domain\Commerce\Services\MerchantOnboardingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MobileCommerceController extends Controller
{
    public function __construct(
        private readonly MerchantOnboardingService $merchantService,
    ) {
    }

    /**
     * List available merchants for the user.
     *
     * GET /api/v1/commerce/merchants
     */
    public function merchants(Request $request): JsonResponse
    {
        $merchants = [
            [
                'id'                => 'merchant_demo_001',
                'display_name'      => 'Demo Coffee Shop',
                'category'          => 'food_beverage',
                'accepted_tokens'   => ['USDC', 'USDT'],
                'accepted_networks' => ['polygon', 'base'],
                'icon_url'          => null,
                'active'            => true,
            ],
            [
                'id'                => 'merchant_demo_002',
                'display_name'      => 'Digital Store',
                'category'          => 'retail',
                'accepted_tokens'   => ['USDC', 'USDT', 'WETH'],
                'accepted_networks' => ['polygon', 'arbitrum', 'base'],
                'icon_url'          => null,
                'active'            => true,
            ],
        ];

        return response()->json([
            'success' => true,
            'data'    => $merchants,
        ]);
    }

    /**
     * Parse a merchant QR code.
     *
     * POST /api/v1/commerce/parse-qr
     */
    public function parseQr(Request $request): JsonResponse
    {
        $request->validate([
            'qr_data' => ['required', 'string'],
        ]);

        $qrData = $request->input('qr_data');

        // Parse QR code format: finaegis://pay?merchant=X&amount=Y&asset=Z&network=N
        $parsed = parse_url($qrData);
        $params = [];
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $params);
        }

        if (empty($params['merchant'])) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'INVALID_QR',
                    'message' => 'Unable to parse QR code.',
                ],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'merchant_id' => $params['merchant'] ?? null,
                'amount'      => $params['amount'] ?? null,
                'asset'       => $params['asset'] ?? 'USDC',
                'network'     => $params['network'] ?? 'polygon',
                'metadata'    => $params,
            ],
        ]);
    }

    /**
     * Create a payment request for a merchant.
     *
     * POST /api/v1/commerce/payment-requests
     */
    public function createPaymentRequest(Request $request): JsonResponse
    {
        $request->validate([
            'merchant_id' => ['required', 'string'],
            'amount'      => ['required', 'string'],
            'asset'       => ['required', 'string', 'in:USDC,USDT,WETH,WBTC'],
            'network'     => ['required', 'string'],
        ]);

        $paymentRequest = [
            'id'          => 'pr_' . Str::random(20),
            'merchant_id' => $request->input('merchant_id'),
            'amount'      => $request->input('amount'),
            'asset'       => $request->input('asset'),
            'network'     => $request->input('network'),
            'status'      => 'pending',
            'created_at'  => now()->toIso8601String(),
            'expires_at'  => now()->addMinutes(15)->toIso8601String(),
        ];

        return response()->json([
            'success' => true,
            'data'    => $paymentRequest,
        ], 201);
    }

    /**
     * Process a commerce payment.
     *
     * POST /api/v1/commerce/payments
     */
    public function processPayment(Request $request): JsonResponse
    {
        $request->validate([
            'payment_request_id' => ['required', 'string'],
        ]);

        $payment = [
            'id'                 => 'pay_' . Str::random(20),
            'payment_request_id' => $request->input('payment_request_id'),
            'status'             => 'processing',
            'created_at'         => now()->toIso8601String(),
        ];

        return response()->json([
            'success' => true,
            'data'    => $payment,
        ], 201);
    }

    /**
     * Generate a payment QR code.
     *
     * POST /api/v1/commerce/generate-qr
     */
    public function generateQr(Request $request): JsonResponse
    {
        $request->validate([
            'amount'  => ['required', 'string'],
            'asset'   => ['required', 'string', 'in:USDC,USDT,WETH,WBTC'],
            'network' => ['required', 'string'],
        ]);

        $user = $request->user();
        $qrData = sprintf(
            'finaegis://pay?to=%s&amount=%s&asset=%s&network=%s',
            'user_' . $user->id,
            $request->input('amount'),
            $request->input('asset'),
            $request->input('network'),
        );

        return response()->json([
            'success' => true,
            'data'    => [
                'qr_data'    => $qrData,
                'expires_at' => now()->addMinutes(30)->toIso8601String(),
            ],
        ]);
    }
}

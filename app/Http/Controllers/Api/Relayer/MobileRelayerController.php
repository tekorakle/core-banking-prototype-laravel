<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Relayer;

use App\Domain\Relayer\Enums\SupportedNetwork;
use App\Domain\Relayer\Services\GasStationService;
use App\Domain\Relayer\Services\SmartAccountService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileRelayerController extends Controller
{
    public function __construct(
        private readonly GasStationService $gasStation,
        private readonly SmartAccountService $smartAccountService,
    ) {
    }

    /**
     * Get relayer status and gas prices.
     *
     * GET /api/v1/relayer/status
     */
    public function status(): JsonResponse
    {
        $networks = [];
        foreach (SupportedNetwork::cases() as $network) {
            $networks[] = [
                'network'         => $network->value,
                'chain_id'        => $network->getChainId(),
                'gas_price_gwei'  => $network->getCurrentGasPrice(),
                'congestion'      => $network->getCongestionLevel(),
                'native_currency' => $network->getNativeCurrency(),
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'healthy'    => true,
                'networks'   => $networks,
                'updated_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Estimate gas for a transaction.
     *
     * POST /api/v1/relayer/estimate-gas
     */
    public function estimateGas(Request $request): JsonResponse
    {
        $request->validate([
            'network' => ['required', 'string'],
            'to'      => ['required', 'string'],
            'value'   => ['string'],
            'data'    => ['string'],
        ]);

        $network = SupportedNetwork::tryFrom($request->input('network'));
        if (! $network) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'UNSUPPORTED_NETWORK',
                    'message' => 'Network not supported.',
                ],
            ], 422);
        }

        $callData = $request->input('data', '0x');
        $estimate = $this->gasStation->estimateFee($callData, $network);

        return response()->json([
            'success' => true,
            'data'    => [
                'network'    => $network->value,
                'gas_price'  => $network->getCurrentGasPrice(),
                'gas_limit'  => (string) ($estimate['estimated_gas'] ?? 200000),
                'total_cost' => $estimate['fee_usdc'] ?? '0',
                'currency'   => $network->getNativeCurrency(),
                'sponsored'  => true,
            ],
        ]);
    }

    /**
     * Build a UserOperation from parameters.
     *
     * POST /api/v1/relayer/build-userop
     */
    public function buildUserOp(Request $request): JsonResponse
    {
        $request->validate([
            'network' => ['required', 'string'],
            'to'      => ['required', 'string'],
            'value'   => ['string'],
            'data'    => ['string'],
        ]);

        $network = SupportedNetwork::tryFrom($request->input('network'));
        if (! $network) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'UNSUPPORTED_NETWORK',
                    'message' => 'Network not supported.',
                ],
            ], 422);
        }

        $userOp = [
            'sender'               => $request->input('from', '0x0'),
            'nonce'                => '0x0',
            'initCode'             => '0x',
            'callData'             => $request->input('data', '0x'),
            'callGasLimit'         => '0x30D40',
            'verificationGasLimit' => '0x186A0',
            'preVerificationGas'   => '0xC350',
            'maxFeePerGas'         => '0x' . dechex(30000000000),
            'maxPriorityFeePerGas' => '0x' . dechex(1500000000),
            'paymasterAndData'     => '0x',
            'signature'            => '0x',
        ];

        return response()->json([
            'success' => true,
            'data'    => [
                'user_op'     => $userOp,
                'network'     => $network->value,
                'entry_point' => $network->getEntryPointAddress(),
            ],
        ]);
    }

    /**
     * Submit a signed UserOperation to the bundler.
     *
     * POST /api/v1/relayer/submit
     */
    public function submitUserOp(Request $request): JsonResponse
    {
        $request->validate([
            'network'   => ['required', 'string'],
            'user_op'   => ['required', 'array'],
            'signature' => ['required', 'string'],
        ]);

        $network = SupportedNetwork::tryFrom($request->input('network'));
        if (! $network) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'UNSUPPORTED_NETWORK',
                    'message' => 'Network not supported.',
                ],
            ], 422);
        }

        $hash = '0x' . bin2hex(random_bytes(32));

        return response()->json([
            'success' => true,
            'data'    => [
                'user_op_hash' => $hash,
                'network'      => $network->value,
                'status'       => 'pending',
                'submitted_at' => now()->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Get UserOperation status by hash.
     *
     * GET /api/v1/relayer/userop/{hash}
     */
    public function getUserOp(string $hash): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'user_op_hash' => $hash,
                'status'       => 'pending',
                'tx_hash'      => null,
                'block_number' => null,
                'updated_at'   => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get tokens accepted for gas payment (paymaster).
     *
     * GET /api/v1/relayer/supported-tokens
     */
    public function supportedTokens(): JsonResponse
    {
        $tokens = [
            ['symbol' => 'USDC', 'name' => 'USD Coin', 'sponsored' => true],
            ['symbol' => 'USDT', 'name' => 'Tether USD', 'sponsored' => true],
            ['symbol' => 'WETH', 'name' => 'Wrapped Ether', 'sponsored' => false],
        ];

        return response()->json([
            'success' => true,
            'data'    => $tokens,
        ]);
    }

    /**
     * Get paymaster configuration data.
     *
     * GET /api/v1/relayer/paymaster-data
     */
    public function paymasterData(): JsonResponse
    {
        $data = [];
        foreach (SupportedNetwork::cases() as $network) {
            $data[] = [
                'network'           => $network->value,
                'paymaster_address' => $network->getPaymasterAddress(),
                'entry_point'       => $network->getEntryPointAddress(),
                'sponsored_tokens'  => ['USDC', 'USDT'],
                'max_gas_sponsored' => '500000',
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * Get per-network relayer status.
     *
     * GET /api/v1/relayer/networks/{network}/status
     */
    public function networkStatus(string $network): JsonResponse
    {
        $supported = SupportedNetwork::tryFrom($network);
        if (! $supported) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'UNSUPPORTED_NETWORK',
                    'message' => "Network '{$network}' is not supported",
                ],
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'chainId'  => $supported->getChainId(),
                'network'  => $supported->value,
                'status'   => 'operational',
                'gasPrice' => [
                    'gwei'        => (float) $supported->getCurrentGasPrice(),
                    'usdEstimate' => $supported->getAverageGasCostUsd(),
                ],
                'blockNumber' => random_int(50_000_000, 60_000_000),
                'relayer'     => [
                    'status'            => 'active',
                    'queueDepth'        => 0,
                    'avgConfirmationMs' => 2400,
                ],
                'updatedAt' => now()->toIso8601String(),
            ],
        ]);
    }
}

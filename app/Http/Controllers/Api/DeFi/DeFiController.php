<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\DeFi;

use App\Domain\CrossChain\Enums\CrossChainNetwork;
use App\Domain\DeFi\Contracts\LendingProtocolInterface;
use App\Domain\DeFi\Contracts\LiquidStakingInterface;
use App\Domain\DeFi\Enums\DeFiProtocol;
use App\Domain\DeFi\Services\DeFiPortfolioService;
use App\Domain\DeFi\Services\DeFiPositionTrackerService;
use App\Domain\DeFi\Services\SwapRouterService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class DeFiController extends Controller
{
    public function __construct(
        private readonly SwapRouterService $swapRouter,
        private readonly DeFiPortfolioService $portfolioService,
        private readonly DeFiPositionTrackerService $positionTracker,
        private readonly LendingProtocolInterface $lendingProtocol,
        private readonly LiquidStakingInterface $stakingProtocol,
    ) {
    }

    /**
     * List supported DeFi protocols with chain availability.
     */
    public function protocols(): JsonResponse
    {
        $protocols = array_map(fn (DeFiProtocol $p) => [
            'name'     => $p->value,
            'display'  => $p->getDisplayName(),
            'category' => $p->getCategory(),
        ], DeFiProtocol::cases());

        return response()->json([
            'success' => true,
            'data'    => array_values($protocols),
        ]);
    }

    /**
     * Get multi-DEX swap quote.
     */
    public function swapQuote(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'chain'      => 'required|string',
            'from_token' => 'required|string|max:20',
            'to_token'   => 'required|string|max:20',
            'amount'     => 'required|string|regex:/^\d+(\.\d+)?$/',
            'slippage'   => 'nullable|numeric|min:0.01|max:50',
        ]);

        try {
            $chain = CrossChainNetwork::from($validated['chain']);
            $slippage = (float) ($validated['slippage'] ?? 0.5);

            $quote = $this->swapRouter->findBestRoute(
                $chain,
                $validated['from_token'],
                $validated['to_token'],
                $validated['amount'],
                $slippage,
            );

            return response()->json([
                'success' => true,
                'data'    => $quote->toArray(),
            ]);
        } catch (Throwable $e) {
            Log::error('DeFi swap quote failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'ERR_DEFI_001',
                    'message' => $e->getMessage(),
                ],
            ], 400);
        }
    }

    /**
     * Execute swap via best route.
     */
    public function swapExecute(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'chain'          => 'required|string',
            'from_token'     => 'required|string|max:20',
            'to_token'       => 'required|string|max:20',
            'amount'         => 'required|string|regex:/^\d+(\.\d+)?$/',
            'wallet_address' => 'required|string|max:100',
            'slippage'       => 'nullable|numeric|min:0.01|max:50',
        ]);

        try {
            $chain = CrossChainNetwork::from($validated['chain']);
            $slippage = (float) ($validated['slippage'] ?? 0.5);

            $quote = $this->swapRouter->findBestRoute(
                $chain,
                $validated['from_token'],
                $validated['to_token'],
                $validated['amount'],
                $slippage,
            );

            $result = $this->swapRouter->executeSwap($quote, $validated['wallet_address']);

            return response()->json([
                'success' => true,
                'data'    => $result,
            ]);
        } catch (Throwable $e) {
            Log::error('DeFi swap execution failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'ERR_DEFI_002',
                    'message' => $e->getMessage(),
                ],
            ], 400);
        }
    }

    /**
     * Get lending markets.
     */
    public function lendingMarkets(Request $request): JsonResponse
    {
        $chain = $request->query('chain', 'ethereum');

        try {
            $network = CrossChainNetwork::from((string) $chain);
            $markets = $this->lendingProtocol->getMarkets($network);

            return response()->json([
                'success' => true,
                'data'    => $markets,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'ERR_DEFI_003',
                    'message' => $e->getMessage(),
                ],
            ], 400);
        }
    }

    /**
     * Get full DeFi portfolio across chains.
     */
    public function portfolio(Request $request): JsonResponse
    {
        $walletAddress = $request->query('wallet_address', '0xDefault');

        $summary = $this->portfolioService->getPortfolioSummary((string) $walletAddress);

        return response()->json([
            'success' => true,
            'data'    => $summary,
        ]);
    }

    /**
     * Get active DeFi positions with optional filters.
     */
    public function positions(Request $request): JsonResponse
    {
        $walletAddress = $request->query('wallet_address', '0xDefault');
        $chain = $request->query('chain');
        $protocol = $request->query('protocol');

        $chainFilter = $chain ? CrossChainNetwork::tryFrom((string) $chain) : null;
        $protocolFilter = $protocol ? DeFiProtocol::tryFrom((string) $protocol) : null;

        $positions = $this->positionTracker->getActivePositions(
            (string) $walletAddress,
            $chainFilter,
            $protocolFilter,
        );

        return response()->json([
            'success' => true,
            'data'    => array_map(fn ($p) => $p->toArray(), $positions),
        ]);
    }

    /**
     * Get liquid staking info and user positions.
     */
    public function staking(Request $request): JsonResponse
    {
        $chain = $request->query('chain', 'ethereum');
        $walletAddress = $request->query('wallet_address', '0xDefault');

        try {
            $network = CrossChainNetwork::from((string) $chain);

            $data = [
                'protocol'       => 'lido',
                'staking_apy'    => $this->stakingProtocol->getStakingAPY($network),
                'staked_balance' => $this->stakingProtocol->getStakedBalance($network, (string) $walletAddress),
            ];

            return response()->json([
                'success' => true,
                'data'    => $data,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'ERR_DEFI_004',
                    'message' => $e->getMessage(),
                ],
            ], 400);
        }
    }

    /**
     * Get yield opportunities across chains.
     */
    public function yield(Request $request): JsonResponse
    {
        $walletAddress = $request->query('wallet_address', '0xDefault');

        $opportunities = $this->portfolioService->getYieldOpportunities((string) $walletAddress);

        return response()->json([
            'success' => true,
            'data'    => $opportunities,
        ]);
    }
}

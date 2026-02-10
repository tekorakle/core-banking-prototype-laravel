<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\CrossChain;

use App\Domain\CrossChain\Enums\CrossChainNetwork;
use App\Domain\CrossChain\Services\BridgeOrchestratorService;
use App\Domain\CrossChain\Services\BridgeTransactionTracker;
use App\Domain\CrossChain\Services\CrossChainSwapService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CrossChainController extends Controller
{
    public function __construct(
        private readonly BridgeOrchestratorService $bridgeOrchestrator,
        private readonly BridgeTransactionTracker $bridgeTracker,
        private readonly CrossChainSwapService $swapService,
    ) {
    }

    /**
     * List supported chains with bridge availability.
     */
    public function chains(): JsonResponse
    {
        $chains = $this->bridgeOrchestrator->getSupportedChains();

        return response()->json([
            'success' => true,
            'data'    => $chains,
        ]);
    }

    /**
     * Get bridge quotes from all providers.
     */
    public function bridgeQuote(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_chain' => 'required|string',
            'to_chain'   => 'required|string',
            'token'      => 'required|string|max:20',
            'amount'     => 'required|string|regex:/^\d+(\.\d+)?$/',
        ]);

        try {
            $sourceChain = CrossChainNetwork::from($validated['from_chain']);
            $destChain = CrossChainNetwork::from($validated['to_chain']);

            $quotes = $this->bridgeOrchestrator->getQuotes(
                $sourceChain,
                $destChain,
                $validated['token'],
                $validated['amount'],
            );

            return response()->json([
                'success' => true,
                'data'    => array_map(fn ($q) => $q->toArray(), $quotes),
            ]);
        } catch (Throwable $e) {
            Log::error('Bridge quote failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'ERR_CROSSCHAIN_001',
                    'message' => $e->getMessage(),
                ],
            ], 400);
        }
    }

    /**
     * Initiate a bridge transfer.
     */
    public function bridgeInitiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_chain'        => 'required|string',
            'to_chain'          => 'required|string',
            'token'             => 'required|string|max:20',
            'amount'            => 'required|string|regex:/^\d+(\.\d+)?$/',
            'sender_address'    => 'required|string|max:100',
            'recipient_address' => 'required|string|max:100',
        ]);

        try {
            $sourceChain = CrossChainNetwork::from($validated['from_chain']);
            $destChain = CrossChainNetwork::from($validated['to_chain']);

            $quote = $this->bridgeOrchestrator->getBestQuote(
                $sourceChain,
                $destChain,
                $validated['token'],
                $validated['amount'],
            );

            $result = $this->bridgeOrchestrator->initiateBridge(
                $quote,
                $validated['sender_address'],
                $validated['recipient_address'],
            );

            return response()->json([
                'success' => true,
                'data'    => [
                    'transaction_id' => $result['transaction_id'],
                    'status'         => $result['status']->value,
                    'quote'          => $quote->toArray(),
                ],
            ]);
        } catch (Throwable $e) {
            Log::error('Bridge initiation failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'ERR_CROSSCHAIN_002',
                    'message' => $e->getMessage(),
                ],
            ], 400);
        }
    }

    /**
     * Get bridge transaction status.
     */
    public function bridgeStatus(string $id): JsonResponse
    {
        $transaction = $this->bridgeTracker->getTransaction($id);

        if ($transaction === null) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'ERR_CROSSCHAIN_003',
                    'message' => 'Bridge transaction not found.',
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $transaction,
        ]);
    }

    /**
     * Get a cross-chain swap quote (bridge + swap).
     */
    public function swapQuote(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_chain' => 'required|string',
            'to_chain'   => 'required|string',
            'from_token' => 'required|string|max:20',
            'to_token'   => 'required|string|max:20',
            'amount'     => 'required|string|regex:/^\d+(\.\d+)?$/',
        ]);

        try {
            $sourceChain = CrossChainNetwork::from($validated['from_chain']);
            $destChain = CrossChainNetwork::from($validated['to_chain']);

            $quote = $this->swapService->getQuote(
                $sourceChain,
                $destChain,
                $validated['from_token'],
                $validated['to_token'],
                $validated['amount'],
            );

            return response()->json([
                'success' => true,
                'data'    => $quote->toArray(),
            ]);
        } catch (Throwable $e) {
            Log::error('Cross-chain swap quote failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'ERR_CROSSCHAIN_004',
                    'message' => $e->getMessage(),
                ],
            ], 400);
        }
    }

    /**
     * Execute a cross-chain swap.
     */
    public function swapExecute(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_chain'     => 'required|string',
            'to_chain'       => 'required|string',
            'from_token'     => 'required|string|max:20',
            'to_token'       => 'required|string|max:20',
            'amount'         => 'required|string|regex:/^\d+(\.\d+)?$/',
            'wallet_address' => 'required|string|max:100',
        ]);

        try {
            $sourceChain = CrossChainNetwork::from($validated['from_chain']);
            $destChain = CrossChainNetwork::from($validated['to_chain']);

            $quote = $this->swapService->getQuote(
                $sourceChain,
                $destChain,
                $validated['from_token'],
                $validated['to_token'],
                $validated['amount'],
            );

            $result = $this->swapService->executeSwap($quote, $validated['wallet_address']);

            return response()->json([
                'success' => true,
                'data'    => $result,
            ]);
        } catch (Throwable $e) {
            Log::error('Cross-chain swap execution failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'ERR_CROSSCHAIN_005',
                    'message' => $e->getMessage(),
                ],
            ], 400);
        }
    }
}

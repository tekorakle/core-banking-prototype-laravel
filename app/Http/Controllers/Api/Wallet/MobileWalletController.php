<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Wallet;

use App\Domain\MobilePayment\Services\ActivityFeedService;
use App\Domain\MobilePayment\Services\PaymentIntentService;
use App\Domain\MobilePayment\Services\TransactionDetailService;
use App\Domain\Relayer\Enums\SupportedNetwork;
use App\Domain\Relayer\Services\SmartAccountService;
use App\Domain\Relayer\Services\WalletBalanceService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class MobileWalletController extends Controller
{
    public function __construct(
        private readonly WalletBalanceService $balanceService,
        private readonly SmartAccountService $smartAccountService,
        private readonly ActivityFeedService $activityFeedService,
        private readonly TransactionDetailService $transactionDetailService,
        private readonly PaymentIntentService $paymentIntentService,
    ) {
    }

    /**
     * Get supported token list with network and decimals info.
     *
     * GET /api/v1/wallet/tokens
     */
    public function tokens(): JsonResponse
    {
        $tokens = [
            [
                'symbol'   => 'USDC',
                'name'     => 'USD Coin',
                'decimals' => 6,
                'networks' => ['polygon', 'base', 'arbitrum', 'optimism', 'ethereum'],
                'icon'     => 'usdc',
            ],
            [
                'symbol'   => 'USDT',
                'name'     => 'Tether USD',
                'decimals' => 6,
                'networks' => ['polygon', 'arbitrum', 'optimism', 'ethereum'],
                'icon'     => 'usdt',
            ],
            [
                'symbol'   => 'WETH',
                'name'     => 'Wrapped Ether',
                'decimals' => 18,
                'networks' => ['polygon', 'base', 'arbitrum', 'optimism'],
                'icon'     => 'weth',
            ],
            [
                'symbol'   => 'WBTC',
                'name'     => 'Wrapped Bitcoin',
                'decimals' => 8,
                'networks' => ['polygon', 'arbitrum', 'ethereum'],
                'icon'     => 'wbtc',
            ],
        ];

        return response()->json([
            'success' => true,
            'data'    => $tokens,
        ]);
    }

    /**
     * Get ERC-20 balances across user's smart accounts.
     *
     * GET /api/v1/wallet/balances
     */
    public function balances(Request $request): JsonResponse
    {
        $user = $request->user();
        $accounts = $this->smartAccountService->getUserAccounts($user);

        $balances = [];
        $supportedTokens = ['USDC', 'USDT', 'WETH', 'WBTC'];

        foreach ($accounts as $account) {
            $networkStr = $account->network ?? 'polygon';
            $network = SupportedNetwork::tryFrom($networkStr);
            if (! $network) {
                continue;
            }
            foreach ($supportedTokens as $token) {
                if (! $this->balanceService->isTokenSupported($token, $network)) {
                    continue;
                }
                try {
                    $balance = $this->balanceService->getBalance(
                        $account->account_address,
                        $token,
                        $network,
                    );
                    $balances[] = [
                        'token'   => $token,
                        'network' => $networkStr,
                        'address' => $account->account_address,
                        'balance' => $balance,
                    ];
                } catch (Throwable) {
                    $balances[] = [
                        'token'   => $token,
                        'network' => $networkStr,
                        'address' => $account->account_address,
                        'balance' => '0',
                        'error'   => 'Balance query failed',
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $balances,
        ]);
    }

    /**
     * Get aggregated wallet state (balances + addresses + sync info).
     *
     * GET /api/v1/wallet/state
     */
    public function state(Request $request): JsonResponse
    {
        $user = $request->user();
        $accounts = $this->smartAccountService->getUserAccounts($user);

        $addresses = [];
        foreach ($accounts as $account) {
            $addresses[] = [
                'address'  => $account->account_address,
                'network'  => $account->network ?? 'polygon',
                'deployed' => $account->is_deployed ?? false,
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'addresses'     => $addresses,
                'networks'      => $this->smartAccountService->getSupportedNetworks(),
                'synced_at'     => now()->toIso8601String(),
                'account_count' => count($accounts),
            ],
        ]);
    }

    /**
     * List user's addresses per network.
     *
     * GET /api/v1/wallet/addresses
     */
    public function addresses(Request $request): JsonResponse
    {
        $user = $request->user();
        $accounts = $this->smartAccountService->getUserAccounts($user);

        $addresses = [];
        foreach ($accounts as $account) {
            $addresses[] = [
                'address'    => $account->account_address,
                'network'    => $account->network ?? 'polygon',
                'deployed'   => $account->is_deployed ?? false,
                'created_at' => $account->created_at?->toIso8601String(),
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $addresses,
        ]);
    }

    /**
     * Cursor-based transaction list from activity feed.
     *
     * GET /api/v1/wallet/transactions
     */
    public function transactions(Request $request): JsonResponse
    {
        $user = $request->user();

        $feed = $this->activityFeedService->getFeed(
            userId: $user->id,
            cursor: $request->query('cursor'),
            limit: min((int) $request->query('limit', '20'), 100),
        );

        return response()->json([
            'success' => true,
            'data'    => $feed,
        ]);
    }

    /**
     * Get transaction detail.
     *
     * GET /api/v1/wallet/transactions/{id}
     */
    public function transactionDetail(string $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $detail = $this->transactionDetailService->getDetails($id, $user->id);

        if (! $detail) {
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
            'data'    => $detail,
        ]);
    }

    /**
     * Create and auto-submit a payment intent (send transaction).
     *
     * POST /api/v1/wallet/transactions/send
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'to'      => ['required', 'string'],
            'token'   => ['required', 'string', 'in:USDC,USDT,WETH,WBTC'],
            'amount'  => ['required', 'string'],
            'network' => ['required', 'string'],
        ]);

        $user = $request->user();

        try {
            $intent = $this->paymentIntentService->create(
                userId: $user->id,
                data: [
                    'recipient_address' => $request->input('to'),
                    'token'             => $request->input('token'),
                    'amount'            => $request->input('amount'),
                    'network'           => $request->input('network'),
                    'type'              => 'send',
                ],
            );

            $intentId = $intent->public_id;

            // Auto-submit the intent
            $result = $this->paymentIntentService->submit($intentId, $user->id, 'wallet');

            return response()->json([
                'success' => true,
                'data'    => $result->toApiResponse(),
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'SEND_FAILED',
                    'message' => $e->getMessage(),
                ],
            ], 422);
        }
    }
}

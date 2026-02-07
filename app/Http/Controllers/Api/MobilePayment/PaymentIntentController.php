<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\MobilePayment;

use App\Domain\MobilePayment\Contracts\PaymentIntentServiceInterface;
use App\Domain\MobilePayment\Exceptions\MerchantNotFoundException;
use App\Domain\MobilePayment\Exceptions\PaymentIntentException;
use App\Http\Controllers\Controller;
use App\Http\Requests\MobilePayment\CreatePaymentIntentRequest;
use App\Http\Requests\MobilePayment\SubmitPaymentIntentRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentIntentController extends Controller
{
    public function __construct(
        private readonly PaymentIntentServiceInterface $paymentIntentService,
    ) {
    }

    /**
     * Create a new payment intent.
     *
     * POST /v1/payments/intents
     */
    public function create(CreatePaymentIntentRequest $request): JsonResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = $request->user();

            $data = $request->validated();

            // Accept idempotency key from header (preferred) or body
            if (! isset($data['idempotencyKey']) && $request->hasHeader('X-Idempotency-Key')) {
                $data['idempotencyKey'] = $request->header('X-Idempotency-Key');
            }

            $intent = $this->paymentIntentService->create(
                $user->id,
                $data,
            );

            return response()->json([
                'success' => true,
                'data'    => $intent->toApiResponse(),
            ], 201);
        } catch (MerchantNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'MERCHANT_NOT_FOUND',
                    'message' => $e->getMessage(),
                ],
            ], 404);
        } catch (PaymentIntentException $e) {
            return response()->json($e->toApiResponse(), $e->httpStatus());
        }
    }

    /**
     * Get payment intent status.
     *
     * GET /v1/payments/intents/{intentId}
     */
    public function show(Request $request, string $intentId): JsonResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = $request->user();

            $intent = $this->paymentIntentService->get(
                $intentId,
                (int) $user->id,
            );

            return response()->json([
                'success' => true,
                'data'    => $intent->toApiResponse(),
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'INTENT_NOT_FOUND',
                    'message' => 'Payment intent not found.',
                ],
            ], 404);
        }
    }

    /**
     * Submit/authorize a payment intent.
     *
     * POST /v1/payments/intents/{intentId}/submit
     */
    public function submit(SubmitPaymentIntentRequest $request, string $intentId): JsonResponse
    {
        try {
            $authType = $request->input('auth', 'biometric');

            /** @var \App\Models\User $user */
            $user = $request->user();

            $intent = $this->paymentIntentService->submit(
                $intentId,
                $user->id,
                $authType,
            );

            // Spec requires minimal response: only intentId + status
            return response()->json([
                'success' => true,
                'data'    => [
                    'intentId' => $intent->public_id,
                    'status'   => strtoupper($intent->status->value),
                ],
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'INTENT_NOT_FOUND',
                    'message' => 'Payment intent not found.',
                ],
            ], 404);
        } catch (PaymentIntentException $e) {
            return response()->json($e->toApiResponse(), $e->httpStatus());
        }
    }

    /**
     * Cancel a payment intent.
     *
     * POST /v1/payments/intents/{intentId}/cancel
     */
    public function cancel(Request $request, string $intentId): JsonResponse
    {
        try {
            $request->validate([
                'reason' => ['sometimes', 'nullable', 'string', 'max:500'],
            ]);
            $reason = $request->input('reason');

            /** @var \App\Models\User $user */
            $user = $request->user();

            $intent = $this->paymentIntentService->cancel(
                $intentId,
                $user->id,
                $reason,
            );

            // Spec requires minimal response: intentId, status, merchant.displayName, amount
            return response()->json([
                'success' => true,
                'data'    => [
                    'intentId' => $intent->public_id,
                    'status'   => strtoupper($intent->status->value),
                    'merchant' => [
                        'displayName' => $intent->merchant?->display_name,
                    ],
                    'amount' => $intent->amount,
                ],
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'INTENT_NOT_FOUND',
                    'message' => 'Payment intent not found.',
                ],
            ], 404);
        } catch (PaymentIntentException $e) {
            return response()->json($e->toApiResponse(), $e->httpStatus());
        }
    }
}

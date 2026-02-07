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
            $intent = $this->paymentIntentService->create(
                $request->user()->id,
                $request->validated(),
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
    public function show(string $intentId): JsonResponse
    {
        try {
            $intent = $this->paymentIntentService->get(
                $intentId,
                (int) request()->user()->id,
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

            $intent = $this->paymentIntentService->submit(
                $intentId,
                $request->user()->id,
                $authType,
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
            $reason = $request->input('reason');

            $intent = $this->paymentIntentService->cancel(
                $intentId,
                $request->user()->id,
                $reason,
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
        } catch (PaymentIntentException $e) {
            return response()->json($e->toApiResponse(), $e->httpStatus());
        }
    }
}

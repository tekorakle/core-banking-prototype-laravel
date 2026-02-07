<?php

declare(strict_types=1);

namespace App\Domain\MobilePayment\Services;

use App\Domain\MobilePayment\Contracts\MerchantLookupServiceInterface;
use App\Domain\MobilePayment\Contracts\PaymentIntentServiceInterface;
use App\Domain\MobilePayment\Enums\PaymentErrorCode;
use App\Domain\MobilePayment\Enums\PaymentIntentStatus;
use App\Domain\MobilePayment\Enums\PaymentNetwork;
use App\Domain\MobilePayment\Exceptions\PaymentIntentException;
use App\Domain\MobilePayment\Models\PaymentIntent;
use Illuminate\Support\Str;

class PaymentIntentService implements PaymentIntentServiceInterface
{
    public function __construct(
        private readonly MerchantLookupServiceInterface $merchantLookup,
        private readonly FeeEstimationService $feeEstimation,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(int $userId, array $data): PaymentIntent
    {
        $merchantId = (string) $data['merchantId'];
        $asset = (string) $data['asset'];
        $network = (string) $data['preferredNetwork'];
        $amount = (string) $data['amount'];
        $shield = (bool) ($data['shield'] ?? false);

        // Look up merchant
        $merchant = $this->merchantLookup->findByPublicId($merchantId);

        // Verify merchant can accept payments
        if (! $merchant->canAcceptPayments()) {
            throw new PaymentIntentException(
                PaymentErrorCode::MERCHANT_UNREACHABLE,
                details: ['merchantId' => $merchantId],
            );
        }

        // Validate merchant accepts this asset+network
        if (! $this->merchantLookup->acceptsPayment($merchant, $asset, $network)) {
            if (! $merchant->acceptsAsset($asset)) {
                throw new PaymentIntentException(
                    PaymentErrorCode::WRONG_TOKEN,
                    details: ['merchantId' => $merchantId, 'asset' => $asset],
                );
            }

            throw new PaymentIntentException(
                PaymentErrorCode::WRONG_NETWORK,
                details: ['merchantId' => $merchantId, 'network' => $network],
            );
        }

        // Estimate fees
        $networkEnum = PaymentNetwork::from($network);
        $fees = $this->feeEstimation->estimate($networkEnum, $amount, $shield);

        // Create the intent
        $expiryMinutes = (int) config('mobile_payment.expiry_minutes', 15);

        $intent = PaymentIntent::create([
            'public_id'              => 'pi_' . Str::random(20),
            'user_id'                => $userId,
            'merchant_id'            => $merchant->id,
            'asset'                  => $asset,
            'network'                => $network,
            'amount'                 => $amount,
            'status'                 => PaymentIntentStatus::AWAITING_AUTH,
            'shield_enabled'         => $shield,
            'fees_estimate'          => $fees->toArray(),
            'required_confirmations' => $networkEnum->requiredConfirmations(),
            'idempotency_key'        => $data['idempotencyKey'] ?? null,
            'expires_at'             => now()->addMinutes($expiryMinutes),
        ]);

        $intent->setRelation('merchant', $merchant);

        return $intent;
    }

    public function get(string $intentId, int $userId): PaymentIntent
    {
        $intent = PaymentIntent::with('merchant')
            ->where('public_id', $intentId)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Lazy expiry check
        $intent->expireIfStale();

        return $intent;
    }

    public function submit(string $intentId, int $userId, string $authType): PaymentIntent
    {
        $intent = $this->get($intentId, $userId);

        // Check if already submitted
        if (in_array($intent->status, [PaymentIntentStatus::SUBMITTING, PaymentIntentStatus::PENDING], true)) {
            throw new PaymentIntentException(
                PaymentErrorCode::INTENT_ALREADY_SUBMITTED,
                details: ['intentId' => $intentId, 'currentStatus' => $intent->status->value],
            );
        }

        // Check if expired
        if ($intent->status === PaymentIntentStatus::EXPIRED) {
            throw new PaymentIntentException(
                PaymentErrorCode::INTENT_EXPIRED,
                details: ['intentId' => $intentId],
            );
        }

        // Transition to SUBMITTING
        $intent->transitionTo(PaymentIntentStatus::SUBMITTING);

        // In demo mode, simulate immediate submission success
        if (config('mobile_payment.demo_mode', true)) {
            $this->simulateDemoSubmission($intent);
        }

        return $intent->fresh(['merchant']) ?? $intent;
    }

    public function cancel(string $intentId, int $userId, ?string $reason = null): PaymentIntent
    {
        $intent = $this->get($intentId, $userId);

        if (! $intent->status->isCancellable()) {
            $errorCode = match (true) {
                $intent->status === PaymentIntentStatus::EXPIRED                                                 => PaymentErrorCode::INTENT_EXPIRED,
                in_array($intent->status, [PaymentIntentStatus::SUBMITTING, PaymentIntentStatus::PENDING], true) => PaymentErrorCode::INTENT_ALREADY_SUBMITTED,
                default                                                                                          => PaymentErrorCode::INTENT_ALREADY_SUBMITTED,
            };

            throw new PaymentIntentException(
                $errorCode,
                details: ['intentId' => $intentId, 'currentStatus' => $intent->status->value],
            );
        }

        $intent->cancel_reason = $reason ?? 'user_cancelled';
        $intent->save();

        $intent->transitionTo(PaymentIntentStatus::CANCELLED);

        return $intent->fresh(['merchant']) ?? $intent;
    }

    /**
     * Simulate a successful demo payment submission.
     */
    private function simulateDemoSubmission(PaymentIntent $intent): void
    {
        $network = PaymentNetwork::from($intent->network);
        $demoTxHash = $this->generateDemoTxHash($network);

        $intent->update([
            'tx_hash'         => $demoTxHash,
            'tx_explorer_url' => $network->explorerUrl($demoTxHash),
            'status'          => PaymentIntentStatus::PENDING,
            'confirmations'   => 0,
        ]);
    }

    private function generateDemoTxHash(PaymentNetwork $network): string
    {
        return match ($network) {
            PaymentNetwork::SOLANA => Str::random(88),
            PaymentNetwork::TRON   => Str::random(64),
        };
    }
}

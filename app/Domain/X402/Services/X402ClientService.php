<?php

declare(strict_types=1);

namespace App\Domain\X402\Services;

use App\Domain\X402\Contracts\X402SignerInterface;
use App\Domain\X402\DataObjects\PaymentPayload;
use App\Domain\X402\DataObjects\PaymentRequirements;
use App\Domain\X402\Models\X402SpendingLimit;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Client-side service for handling x402 payments when FinAegis
 * acts as a buyer (e.g., AI agents paying external APIs).
 */
class X402ClientService
{
    public function __construct(
        private readonly X402SignerInterface $signer,
        private readonly X402HeaderCodecService $codec,
    ) {
    }

    /**
     * Handle a 402 Payment Required response from an external API.
     *
     * Parses the requirements, checks spending limits, signs payment,
     * and returns the PAYMENT-SIGNATURE header for retry.
     *
     * @return array<string, string> Headers to attach to the retry request
     */
    public function handlePaymentRequired(
        string $paymentRequiredHeader,
        string $agentId,
    ): array {
        $paymentRequired = $this->codec->decodePaymentRequired($paymentRequiredHeader);

        // Select the best payment option
        $selected = $this->selectPaymentOption($paymentRequired->accepts);
        if ($selected === null) {
            throw new RuntimeException('No supported payment method in x402 requirements');
        }

        // Enforce spending limits
        $this->enforceSpendingLimit($agentId, $selected->amount, $selected->network);

        // Create signed payment payload
        $signedPayload = $this->signer->signTransferAuthorization(
            network: $selected->network,
            to: $selected->payTo,
            amount: $selected->amount,
            asset: $selected->asset,
            maxTimeoutSeconds: $selected->maxTimeoutSeconds,
            extra: $selected->extra,
        );

        $paymentPayload = new PaymentPayload(
            x402Version: $paymentRequired->x402Version,
            resource: $paymentRequired->resource,
            accepted: $selected,
            payload: $signedPayload,
        );

        Log::info('x402: Created payment for external API', [
            'agent_id' => $agentId,
            'network'  => $selected->network,
            'amount'   => $selected->amount,
            'url'      => $paymentRequired->resource->url,
        ]);

        return [
            'PAYMENT-SIGNATURE' => $paymentPayload->toBase64(),
        ];
    }

    /**
     * Select the best payment option from available requirements.
     *
     * Prefers Base mainnet, then Base Sepolia (testnet), then other EVM.
     */
    private function selectPaymentOption(array $accepts): ?PaymentRequirements
    {
        $preferred = ['eip155:8453', 'eip155:84532', 'eip155:1'];

        foreach ($preferred as $network) {
            foreach ($accepts as $option) {
                if ($option->network === $network && $option->scheme === 'exact') {
                    return $option;
                }
            }
        }

        // Fallback: first EVM option
        foreach ($accepts as $option) {
            if (str_starts_with($option->network, 'eip155:')) {
                return $option;
            }
        }

        return null;
    }

    /**
     * Enforce agent spending limits before authorizing a payment.
     *
     * @throws RuntimeException If spending limit would be exceeded
     */
    private function enforceSpendingLimit(string $agentId, string $amount, string $network): void
    {
        $limit = X402SpendingLimit::where('agent_id', $agentId)->first();

        if ($limit === null) {
            // Check against global config limits
            $maxAutoPay = (string) config('x402.client.max_auto_pay_amount', '100000');
            if (bccomp($amount, $maxAutoPay) > 0) {
                throw new RuntimeException(
                    "Payment amount {$amount} exceeds auto-pay limit {$maxAutoPay}. Human approval required."
                );
            }

            return;
        }

        $limit->resetIfNeeded();

        if (! $limit->canSpend($amount)) {
            throw new RuntimeException(
                "Payment amount {$amount} would exceed agent daily limit. Remaining: {$limit->remainingDailyBudget()}"
            );
        }

        if (! $limit->auto_pay_enabled && bccomp($amount, (string) config('x402.agent_spending.require_approval_above', '1000000')) > 0) {
            throw new RuntimeException(
                'Payment amount exceeds auto-pay threshold. Human approval required.'
            );
        }

        // Record spending
        $limit->recordSpending($amount);
    }
}

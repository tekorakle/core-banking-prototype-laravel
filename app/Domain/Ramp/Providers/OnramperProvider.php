<?php

declare(strict_types=1);

namespace App\Domain\Ramp\Providers;

use App\Domain\Ramp\Clients\OnramperClient;
use App\Domain\Ramp\Contracts\RampProviderInterface;
use RuntimeException;

class OnramperProvider implements RampProviderInterface
{
    public function __construct(
        private readonly OnramperClient $client,
    ) {
    }

    public function createSession(array $params): array
    {
        $type = $params['type'];
        $fiatCurrency = strtolower($params['fiat_currency']);
        $cryptoCurrency = strtolower($params['crypto_currency']);
        $amount = $params['fiat_amount'];
        $walletAddress = $params['wallet_address'];

        // Build widget URL with appropriate parameters
        $widgetParams = [
            'defaultAmount' => $amount,
            'walletAddress' => $walletAddress,
        ];

        if ($type === 'on') {
            $widgetParams['mode'] = 'buy';
            $widgetParams['defaultFiat'] = strtoupper($fiatCurrency);
            $widgetParams['defaultCrypto'] = $cryptoCurrency;
            $widgetParams['onlyCryptos'] = $cryptoCurrency;
        } else {
            $widgetParams['mode'] = 'sell';
            $widgetParams['sell_defaultFiat'] = strtoupper($fiatCurrency);
            $widgetParams['sell_defaultCrypto'] = $cryptoCurrency;
            $widgetParams['sell_onlyCryptos'] = $cryptoCurrency;
        }

        // Add redirect URLs if configured
        $successUrl = config('ramp.providers.onramper.success_redirect_url');
        $failureUrl = config('ramp.providers.onramper.failure_redirect_url');

        if ($successUrl) {
            $widgetParams['successRedirectUrl'] = $successUrl;
        }
        if ($failureUrl) {
            $widgetParams['failureRedirectUrl'] = $failureUrl;
        }

        // Add a partner context for webhook correlation
        $partnerContext = 'finaegis_' . bin2hex(random_bytes(16));
        $widgetParams['partnerContext'] = $partnerContext;

        // Sign the widget URL (required when wallet addresses are present)
        $widgetUrl = $this->client->buildWidgetUrl($widgetParams);
        $queryString = (string) parse_url($widgetUrl, PHP_URL_QUERY);
        $signature = $this->client->signPayload($queryString);
        $widgetUrl .= '&signature=' . $signature;

        return [
            'session_id'    => $partnerContext,
            'redirect_url'  => $widgetUrl,
            'widget_config' => [
                'provider'        => 'onramper',
                'widget_url'      => $widgetUrl,
                'partner_context' => $partnerContext,
                'type'            => $type,
                'mode'            => $type === 'on' ? 'buy' : 'sell',
            ],
        ];
    }

    public function getSessionStatus(string $sessionId): array
    {
        try {
            $transaction = $this->client->getTransaction($sessionId);

            $status = $this->mapOnramperStatus($transaction['status'] ?? 'unknown');

            return [
                'status'        => $status,
                'fiat_amount'   => (float) ($transaction['fiatAmount'] ?? 0),
                'crypto_amount' => (float) ($transaction['cryptoAmount'] ?? 0),
                'metadata'      => [
                    'provider'        => 'onramper',
                    'onramper_id'     => $transaction['id'] ?? $sessionId,
                    'payment_method'  => $transaction['paymentMethod'] ?? null,
                    'onramp_provider' => $transaction['provider'] ?? null,
                ],
            ];
        } catch (RuntimeException) {
            // If transaction not found, it's still pending (widget not completed yet)
            return [
                'status'        => 'pending',
                'fiat_amount'   => null,
                'crypto_amount' => null,
                'metadata'      => ['provider' => 'onramper'],
            ];
        }
    }

    public function getSupportedCurrencies(): array
    {
        $pairs = [];
        $fiats = config('ramp.supported_fiat', ['USD', 'EUR', 'GBP']);
        $cryptos = config('ramp.supported_crypto', ['USDC', 'USDT', 'ETH', 'BTC']);

        foreach ($fiats as $fiat) {
            foreach ($cryptos as $crypto) {
                $pairs[] = ['fiat' => $fiat, 'crypto' => $crypto];
            }
        }

        return $pairs;
    }

    public function getQuote(string $type, string $fiatCurrency, float $fiatAmount, string $cryptoCurrency): array
    {
        $source = strtolower($fiatCurrency);
        $destination = strtolower($cryptoCurrency);

        if ($type === 'off') {
            // Off-ramp: selling crypto for fiat — swap source/destination
            [$source, $destination] = [$destination, $source];
        }

        $quotes = $this->client->getQuotes($source, $destination, $fiatAmount);

        if (empty($quotes)) {
            throw new RuntimeException('No quotes available for this currency pair.');
        }

        // Pick the best quote (first one — Onramper returns sorted by best rate)
        $best = is_array($quotes[0] ?? null) ? $quotes[0] : $quotes;

        $cryptoAmount = (float) ($best['cryptoAmount'] ?? $best['payout'] ?? 0);
        $fiatFee = (float) ($best['fee']['fiatFee'] ?? $best['totalFee'] ?? 0);
        $networkFee = (float) ($best['fee']['networkFee'] ?? 0);
        $totalFee = $fiatFee + $networkFee;
        $exchangeRate = $fiatAmount > 0 ? $cryptoAmount / ($fiatAmount - $totalFee) : 0;

        return [
            'fiat_amount'   => $fiatAmount,
            'crypto_amount' => round($cryptoAmount, 8),
            'exchange_rate' => round($exchangeRate, 8),
            'fee'           => round($totalFee, 2),
            'fee_currency'  => $fiatCurrency,
            'provider_name' => $best['provider'] ?? 'onramper',
            'quote_id'      => $best['quoteId'] ?? null,
        ];
    }

    public function getWebhookValidator(): callable
    {
        return fn (string $payload, string $signature): bool => $this->client->verifyWebhookSignature($payload, $signature);
    }

    public function getName(): string
    {
        return 'onramper';
    }

    /**
     * Map Onramper transaction statuses to internal statuses.
     */
    private function mapOnramperStatus(string $status): string
    {
        return match (strtolower($status)) {
            'completed', 'success', 'done' => 'completed',
            'failed', 'error', 'cancelled', 'expired' => 'failed',
            'pending', 'new', 'created' => 'pending',
            default => 'processing',
        };
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Ramp;

use App\Domain\Ramp\Clients\OnramperClient;
use App\Domain\Ramp\Providers\OnramperProvider;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class OnramperProviderTest extends TestCase
{
    private OnramperClient&MockInterface $client;

    private OnramperProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var OnramperClient&MockInterface $client */
        $client = Mockery::mock(OnramperClient::class);
        $this->client = $client;
        $this->provider = new OnramperProvider($this->client);
    }

    public function test_get_name_returns_onramper(): void
    {
        $this->assertEquals('onramper', $this->provider->getName());
    }

    public function test_get_quote_returns_best_quote(): void
    {
        $this->client->shouldReceive('getQuotes')
            ->with('usd', 'btc', 100.0)
            ->once()
            ->andReturn([
                [
                    'provider'     => 'Simplex',
                    'quoteId'      => 'q_12345',
                    'fiatAmount'   => 100,
                    'cryptoAmount' => 0.0025,
                    'exchangeRate' => 40000,
                    'fee'          => ['fiatFee' => 3.5, 'networkFee' => 0.5],
                ],
                [
                    'provider'     => 'MoonPay',
                    'quoteId'      => 'q_67890',
                    'fiatAmount'   => 100,
                    'cryptoAmount' => 0.0024,
                    'exchangeRate' => 39500,
                    'fee'          => ['fiatFee' => 4.0, 'networkFee' => 0.5],
                ],
            ]);

        $quote = $this->provider->getQuote('on', 'USD', 100.0, 'BTC');

        $this->assertEquals(100.0, $quote['fiat_amount']);
        $this->assertEquals(0.0025, $quote['crypto_amount']);
        $this->assertEquals(4.0, $quote['fee']);
        $this->assertEquals('USD', $quote['fee_currency']);
        $this->assertEquals('Simplex', $quote['provider_name']);
        $this->assertEquals('q_12345', $quote['quote_id']);
    }

    public function test_get_quote_off_ramp_swaps_currencies(): void
    {
        $this->client->shouldReceive('getQuotes')
            ->with('btc', 'usd', 0.005)
            ->once()
            ->andReturn([
                [
                    'provider'     => 'Transak',
                    'cryptoAmount' => 195.0,
                    'fee'          => ['fiatFee' => 2.0, 'networkFee' => 0.5],
                ],
            ]);

        $quote = $this->provider->getQuote('off', 'USD', 0.005, 'BTC');

        $this->assertEquals(0.005, $quote['fiat_amount']);
        $this->assertEquals(195.0, $quote['crypto_amount']);
    }

    public function test_get_quote_throws_when_no_quotes(): void
    {
        $this->client->shouldReceive('getQuotes')
            ->once()
            ->andReturn([]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No quotes available');

        $this->provider->getQuote('on', 'USD', 100.0, 'BTC');
    }

    public function test_create_session_returns_widget_url(): void
    {
        $this->client->shouldReceive('buildWidgetUrl')
            ->once()
            ->andReturn('https://buy.onramper.com?apiKey=pk_test&mode=buy&defaultFiat=USD&defaultCrypto=usdc');

        $this->client->shouldReceive('signPayload')
            ->once()
            ->andReturn('abc123signature');

        $result = $this->provider->createSession([
            'type'            => 'on',
            'fiat_currency'   => 'USD',
            'fiat_amount'     => 100.0,
            'crypto_currency' => 'USDC',
            'wallet_address'  => '0x1234',
        ]);

        $this->assertArrayHasKey('session_id', $result);
        $this->assertStringStartsWith('finaegis_', $result['session_id']);
        $this->assertStringContainsString('buy.onramper.com', $result['redirect_url']);
        $this->assertStringContainsString('signature=abc123signature', $result['redirect_url']);
        $this->assertEquals('onramper', $result['widget_config']['provider']);
        $this->assertEquals('buy', $result['widget_config']['mode']);
    }

    public function test_create_session_off_ramp_uses_sell_mode(): void
    {
        $this->client->shouldReceive('buildWidgetUrl')
            ->withArgs(function (array $params) {
                return $params['mode'] === 'sell'
                    && isset($params['sell_defaultFiat'])
                    && isset($params['sell_defaultCrypto']);
            })
            ->once()
            ->andReturn('https://buy.onramper.com?mode=sell');

        $this->client->shouldReceive('signPayload')
            ->once()
            ->andReturn('sig');

        $result = $this->provider->createSession([
            'type'            => 'off',
            'fiat_currency'   => 'EUR',
            'fiat_amount'     => 50.0,
            'crypto_currency' => 'ETH',
            'wallet_address'  => '0xabc',
        ]);

        $this->assertEquals('sell', $result['widget_config']['mode']);
    }

    public function test_get_session_status_maps_completed(): void
    {
        $this->client->shouldReceive('getTransaction')
            ->with('tx_123')
            ->once()
            ->andReturn([
                'id'            => 'tx_123',
                'status'        => 'completed',
                'fiatAmount'    => 100.0,
                'cryptoAmount'  => 98.5,
                'paymentMethod' => 'creditCard',
                'provider'      => 'Simplex',
            ]);

        $result = $this->provider->getSessionStatus('tx_123');

        $this->assertEquals('completed', $result['status']);
        $this->assertEquals(100.0, $result['fiat_amount']);
        $this->assertEquals(98.5, $result['crypto_amount']);
        $this->assertEquals('Simplex', $result['metadata']['onramp_provider']);
    }

    public function test_get_session_status_maps_failed(): void
    {
        $this->client->shouldReceive('getTransaction')
            ->with('tx_456')
            ->once()
            ->andReturn(['status' => 'failed']);

        $result = $this->provider->getSessionStatus('tx_456');

        $this->assertEquals('failed', $result['status']);
    }

    public function test_get_session_status_returns_pending_on_error(): void
    {
        $this->client->shouldReceive('getTransaction')
            ->with('tx_notfound')
            ->once()
            ->andThrow(new \RuntimeException('Transaction not found'));

        $result = $this->provider->getSessionStatus('tx_notfound');

        $this->assertEquals('pending', $result['status']);
    }

    public function test_get_supported_currencies_returns_pairs(): void
    {
        $currencies = $this->provider->getSupportedCurrencies();

        $this->assertNotEmpty($currencies);
        $this->assertArrayHasKey('fiat', $currencies[0]);
        $this->assertArrayHasKey('crypto', $currencies[0]);
    }

    public function test_webhook_validator_delegates_to_client(): void
    {
        $this->client->shouldReceive('verifyWebhookSignature')
            ->with('{"id":"tx_1"}', 'valid_sig')
            ->once()
            ->andReturn(true);

        $validator = $this->provider->getWebhookValidator();

        $this->assertTrue($validator('{"id":"tx_1"}', 'valid_sig'));
    }

    public function test_webhook_validator_rejects_invalid_signature(): void
    {
        $this->client->shouldReceive('verifyWebhookSignature')
            ->with('{"id":"tx_1"}', 'bad_sig')
            ->once()
            ->andReturn(false);

        $validator = $this->provider->getWebhookValidator();

        $this->assertFalse($validator('{"id":"tx_1"}', 'bad_sig'));
    }
}

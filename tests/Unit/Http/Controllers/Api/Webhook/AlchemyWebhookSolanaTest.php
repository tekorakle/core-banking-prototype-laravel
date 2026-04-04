<?php

declare(strict_types=1);

use App\Domain\Account\Models\BlockchainAddress;
use App\Domain\Account\Models\BlockchainTransaction;
use App\Domain\MobilePayment\Models\ActivityFeedItem;
use App\Domain\Wallet\Services\HeliusTransactionProcessor;
use App\Http\Controllers\Api\Webhook\AlchemyWebhookController;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;
use Tests\Traits\CreatesSolanaTestTables;

uses(TestCase::class, CreatesSolanaTestTables::class);

beforeEach(function (): void {
    config(['relayer.alchemy_webhook_signing_keys' => ['test-signing-key']]);
    config(['cache.default' => 'array']);
    $this->createSolanaTestTables();
});

afterEach(function (): void {
    $this->dropSolanaTestTables();
});

/**
 * Build an Alchemy ADDRESS_ACTIVITY webhook payload for a Solana SPL token transfer.
 *
 * @return array<string, mixed>
 */
function makeAlchemySolanaTokenPayload(
    string $hash,
    string $from,
    string $to,
    string $value = '25.50',
    string $mint = 'EPjFWdd5AufqSSqeM2qN1xzybapC8G4wEGGkZwyTDt1v',
    string $network = 'sol-mainnet',
): array {
    return [
        'type'  => 'ADDRESS_ACTIVITY',
        'event' => [
            'network'  => $network,
            'activity' => [
                [
                    'category'    => 'token',
                    'hash'        => $hash,
                    'fromAddress' => $from,
                    'toAddress'   => $to,
                    'value'       => $value,
                    'asset'       => 'USDC',
                    'rawContract' => [
                        'address' => $mint,
                    ],
                ],
            ],
        ],
    ];
}

/**
 * Build an Alchemy ADDRESS_ACTIVITY webhook payload for a native SOL transfer.
 *
 * @return array<string, mixed>
 */
function makeAlchemySolanaNativePayload(
    string $hash,
    string $from,
    string $to,
    string $solValue = '2.0',
    string $network = 'sol-mainnet',
): array {
    return [
        'type'  => 'ADDRESS_ACTIVITY',
        'event' => [
            'network'  => $network,
            'activity' => [
                [
                    'category'    => 'external',
                    'hash'        => $hash,
                    'fromAddress' => $from,
                    'toAddress'   => $to,
                    'value'       => $solValue,
                    'asset'       => 'SOL',
                ],
            ],
        ],
    ];
}

/**
 * Create a signed request for the Alchemy webhook.
 *
 * @param array<string, mixed> $payload
 */
function makeSignedAlchemyRequest(array $payload, string $signingKey = 'test-signing-key'): Request
{
    $body = (string) json_encode($payload);
    $signature = hash_hmac('sha256', $body, $signingKey);

    $request = Request::create('/api/webhooks/alchemy/address-activity', 'POST', [], [], [], [
        'HTTP_X_ALCHEMY_SIGNATURE' => $signature,
        'CONTENT_TYPE'             => 'application/json',
    ], $body);

    return $request;
}

it('stores incoming USDC SPL transfer on Solana via Alchemy', function (): void {
    $user = User::factory()->create();
    BlockchainAddress::create([
        'user_uuid'  => $user->uuid,
        'chain'      => 'solana',
        'address'    => '7xKXtg2CW87d97TXJSDpbD5jBkheTqA83TZRuJosgAsU',
        'public_key' => '7xKXtg2CW87d97TXJSDpbD5jBkheTqA83TZRuJosgAsU',
        'is_active'  => true,
    ]);

    $payload = makeAlchemySolanaTokenPayload(
        'alchemy_sol_sig_001',
        'ExternalSender123',
        '7xKXtg2CW87d97TXJSDpbD5jBkheTqA83TZRuJosgAsU',
        '25.50',
    );

    $controller = app(AlchemyWebhookController::class);
    $request = makeSignedAlchemyRequest($payload);
    $response = $controller->handle($request);

    $data = $response->getData(true);
    expect($data['status'])->toBe('processed');
    expect($data['users_notified'])->toBeGreaterThan(0);

    // Verify blockchain transaction stored
    $btx = BlockchainTransaction::where('tx_hash', 'alchemy_sol_sig_001')->first();
    expect($btx)->not->toBeNull();
    assert($btx instanceof BlockchainTransaction);
    expect($btx->type)->toBe('receive');
    expect($btx->chain)->toBe('solana');
    expect($btx->status)->toBe('confirmed');
    expect($btx->from_address)->toBe('ExternalSender123');
    expect($btx->to_address)->toBe('7xKXtg2CW87d97TXJSDpbD5jBkheTqA83TZRuJosgAsU');

    // Verify activity feed item stored
    $feedItem = ActivityFeedItem::where(
        'reference_id',
        HeliusTransactionProcessor::signatureToUuid('alchemy_sol_sig_001')
    )->first();
    expect($feedItem)->not->toBeNull();
    assert($feedItem instanceof ActivityFeedItem);
    expect($feedItem->activity_type->value)->toBe('transfer_in');
    expect($feedItem->asset)->toBe('USDC');
    expect($feedItem->network)->toBe('solana');
    expect($feedItem->status)->toBe('confirmed');
    expect((int) $feedItem->user_id)->toBe($user->id);
});

it('stores native SOL transfer on Solana via Alchemy', function (): void {
    $user = User::factory()->create();
    BlockchainAddress::create([
        'user_uuid'  => $user->uuid,
        'chain'      => 'solana',
        'address'    => 'SolReceiverAddr456',
        'public_key' => 'SolReceiverAddr456',
        'is_active'  => true,
    ]);

    $payload = makeAlchemySolanaNativePayload(
        'alchemy_sol_native_001',
        'NativeSender789',
        'SolReceiverAddr456',
        '2.5',
    );

    $controller = app(AlchemyWebhookController::class);
    $request = makeSignedAlchemyRequest($payload);
    $response = $controller->handle($request);

    expect($response->getData(true)['users_notified'])->toBeGreaterThan(0);

    // Verify blockchain transaction stored
    $btx = BlockchainTransaction::where('tx_hash', 'alchemy_sol_native_001')->first();
    expect($btx)->not->toBeNull();
    assert($btx instanceof BlockchainTransaction);
    expect($btx->type)->toBe('receive');
    expect($btx->chain)->toBe('solana');

    // Verify activity feed item with SOL asset
    $feedItem = ActivityFeedItem::where(
        'reference_id',
        HeliusTransactionProcessor::signatureToUuid('alchemy_sol_native_001')
    )->first();
    expect($feedItem)->not->toBeNull();
    assert($feedItem instanceof ActivityFeedItem);
    expect($feedItem->asset)->toBe('SOL');
    expect((float) $feedItem->amount)->toBeGreaterThan(2.0);
});

it('ignores unknown Solana addresses in Alchemy webhook', function (): void {
    $payload = makeAlchemySolanaTokenPayload(
        'alchemy_unknown_001',
        'UnknownFrom',
        'UnknownTo',
    );

    $controller = app(AlchemyWebhookController::class);
    $request = makeSignedAlchemyRequest($payload);
    $response = $controller->handle($request);

    expect($response->getData(true)['users_notified'])->toBe(0);
    expect(BlockchainTransaction::where('tx_hash', 'alchemy_unknown_001')->count())->toBe(0);
    expect(ActivityFeedItem::count())->toBe(0);
});

it('deduplicates Solana transactions on retry via Alchemy', function (): void {
    $user = User::factory()->create();
    BlockchainAddress::create([
        'user_uuid'  => $user->uuid,
        'chain'      => 'solana',
        'address'    => 'DedupeAddr123',
        'public_key' => 'DedupeAddr123',
        'is_active'  => true,
    ]);

    $payload = makeAlchemySolanaTokenPayload(
        'alchemy_dedupe_001',
        'Sender',
        'DedupeAddr123',
    );

    $controller = app(AlchemyWebhookController::class);

    // Send the same webhook twice
    $request1 = makeSignedAlchemyRequest($payload);
    $controller->handle($request1);

    $request2 = makeSignedAlchemyRequest($payload);
    $controller->handle($request2);

    expect(BlockchainTransaction::where('tx_hash', 'alchemy_dedupe_001')->count())->toBe(1);
    expect(
        ActivityFeedItem::where(
            'reference_id',
            HeliusTransactionProcessor::signatureToUuid('alchemy_dedupe_001')
        )->count()
    )->toBe(1);
});

it('verifies HMAC signature for Solana Alchemy webhook', function (): void {
    $payload = makeAlchemySolanaTokenPayload(
        'alchemy_auth_001',
        'From',
        'To',
    );

    $controller = app(AlchemyWebhookController::class);

    // Send with wrong signature
    $body = (string) json_encode($payload);
    $wrongSignature = hash_hmac('sha256', $body, 'wrong-key');

    $request = Request::create('/api/webhooks/alchemy/address-activity', 'POST', [], [], [], [
        'HTTP_X_ALCHEMY_SIGNATURE' => $wrongSignature,
        'CONTENT_TYPE'             => 'application/json',
    ], $body);

    $response = $controller->handle($request);

    expect($response->getStatusCode())->toBe(401);
    expect($response->getData(true)['error'])->toBe('Invalid signature');
});

it('preserves case sensitivity for Solana addresses', function (): void {
    $user = User::factory()->create();
    $caseSensitiveAddr = 'SoLaNaAdDrEsSwItHmIxEdCaSe123';
    BlockchainAddress::create([
        'user_uuid'  => $user->uuid,
        'chain'      => 'solana',
        'address'    => $caseSensitiveAddr,
        'public_key' => $caseSensitiveAddr,
        'is_active'  => true,
    ]);

    $payload = makeAlchemySolanaTokenPayload(
        'alchemy_case_001',
        'Sender',
        $caseSensitiveAddr,
    );

    $controller = app(AlchemyWebhookController::class);
    $request = makeSignedAlchemyRequest($payload);
    $response = $controller->handle($request);

    expect($response->getData(true)['users_notified'])->toBeGreaterThan(0);

    $btx = BlockchainTransaction::where('tx_hash', 'alchemy_case_001')->first();
    expect($btx)->not->toBeNull();
    assert($btx instanceof BlockchainTransaction);
    expect($btx->to_address)->toBe($caseSensitiveAddr);
});

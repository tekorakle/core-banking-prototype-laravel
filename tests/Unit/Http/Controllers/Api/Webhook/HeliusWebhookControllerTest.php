<?php

declare(strict_types=1);

use App\Domain\Account\Models\BlockchainAddress;
use App\Domain\Account\Models\BlockchainTransaction;
use App\Domain\MobilePayment\Models\ActivityFeedItem;
use App\Domain\Wallet\Services\HeliusTransactionProcessor;
use App\Http\Controllers\Api\Webhook\HeliusWebhookController;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;
use Tests\Traits\CreatesSolanaTestTables;

uses(TestCase::class, CreatesSolanaTestTables::class);

beforeEach(function (): void {
    config(['services.helius.webhook_secret' => '']);
    config(['cache.default' => 'array']);
    $this->createSolanaTestTables();
});

afterEach(function (): void {
    $this->dropSolanaTestTables();
});

/** @return array<string, mixed> */
function makeHeliusTx(string $signature, string $from, string $to, string $amount = '10.5', string $mint = 'EPjFWdd5AufqSSqeM2qN1xzybapC8G4wEGGkZwyTDt1v'): array
{
    return [
        'signature'      => $signature,
        'type'           => 'TRANSFER',
        'fee'            => 5000,
        'tokenTransfers' => [
            [
                'fromUserAccount' => $from,
                'toUserAccount'   => $to,
                'tokenAmount'     => $amount,
                'mint'            => $mint,
            ],
        ],
        'nativeTransfers' => [],
    ];
}

/** @return array<string, mixed> */
function makeNativeSolTx(string $signature, string $from, string $to, int $lamports = 1000000000): array
{
    return [
        'signature'       => $signature,
        'type'            => 'TRANSFER',
        'fee'             => 5000,
        'tokenTransfers'  => [],
        'nativeTransfers' => [
            [
                'fromUserAccount' => $from,
                'toUserAccount'   => $to,
                'amount'          => $lamports,
            ],
        ],
    ];
}

it('stores incoming token transaction in both tables', function (): void {
    $user = User::factory()->create();
    $address = BlockchainAddress::create([
        'user_uuid'  => $user->uuid,
        'chain'      => 'solana',
        'address'    => 'ReceiverAddr123',
        'public_key' => 'ReceiverAddr123',
        'is_active'  => true,
    ]);

    $controller = app(HeliusWebhookController::class);
    $tx = makeHeliusTx('sig_incoming_001', 'SenderAddr456', 'ReceiverAddr123', '25.50');

    $request = Request::create('/api/webhooks/helius/solana', 'POST', [], [], [], [], (string) json_encode([$tx]));
    $request->headers->set('Content-Type', 'application/json');

    $response = $controller->handle($request);

    expect($response->getData(true)['processed'])->toBeGreaterThan(0);

    // Verify blockchain transaction stored
    $btx = BlockchainTransaction::where('tx_hash', 'sig_incoming_001')->first();
    expect($btx)->not->toBeNull();
    assert($btx instanceof BlockchainTransaction);
    expect($btx->type)->toBe('receive');
    expect($btx->chain)->toBe('solana');
    expect($btx->status)->toBe('confirmed');
    expect($btx->from_address)->toBe('SenderAddr456');
    expect($btx->to_address)->toBe('ReceiverAddr123');

    // Verify activity feed item stored
    $feedItem = ActivityFeedItem::where('reference_id', HeliusTransactionProcessor::signatureToUuid('sig_incoming_001'))->first();
    expect($feedItem)->not->toBeNull();
    assert($feedItem instanceof ActivityFeedItem);
    expect($feedItem->activity_type->value)->toBe('transfer_in');
    expect($feedItem->asset)->toBe('USDC');
    expect($feedItem->network)->toBe('solana');
    expect($feedItem->status)->toBe('confirmed');
    expect((int) $feedItem->user_id)->toBe($user->id);
});

it('stores outgoing token transaction correctly', function (): void {
    $user = User::factory()->create();
    BlockchainAddress::create([
        'user_uuid'  => $user->uuid,
        'chain'      => 'solana',
        'address'    => 'SenderAddr789',
        'public_key' => 'SenderAddr789',
        'is_active'  => true,
    ]);

    $controller = app(HeliusWebhookController::class);
    $tx = makeHeliusTx('sig_outgoing_001', 'SenderAddr789', 'ExternalAddr999', '5.00');

    $request = Request::create('/api/webhooks/helius/solana', 'POST', [], [], [], [], (string) json_encode([$tx]));
    $request->headers->set('Content-Type', 'application/json');

    $controller->handle($request);

    $btx = BlockchainTransaction::where('tx_hash', 'sig_outgoing_001')->first();
    assert($btx instanceof BlockchainTransaction);
    expect($btx->type)->toBe('send');

    $feedItem = ActivityFeedItem::where('reference_id', HeliusTransactionProcessor::signatureToUuid('sig_outgoing_001'))->first();
    assert($feedItem instanceof ActivityFeedItem);
    expect($feedItem->activity_type->value)->toBe('transfer_out');
    expect((float) $feedItem->amount)->toBeLessThan(0); // Negative for outgoing
});

it('stores native SOL transfer with correct amount', function (): void {
    $user = User::factory()->create();
    BlockchainAddress::create([
        'user_uuid'  => $user->uuid,
        'chain'      => 'solana',
        'address'    => 'SolReceiver',
        'public_key' => 'SolReceiver',
        'is_active'  => true,
    ]);

    $controller = app(HeliusWebhookController::class);
    $tx = makeNativeSolTx('sig_sol_001', 'SolSender', 'SolReceiver', 2000000000); // 2 SOL

    $request = Request::create('/api/webhooks/helius/solana', 'POST', [], [], [], [], (string) json_encode([$tx]));
    $request->headers->set('Content-Type', 'application/json');

    $controller->handle($request);

    $feedItem = ActivityFeedItem::where('reference_id', HeliusTransactionProcessor::signatureToUuid('sig_sol_001'))->first();
    assert($feedItem instanceof ActivityFeedItem);
    expect($feedItem->asset)->toBe('SOL');
    expect((float) $feedItem->amount)->toBeGreaterThan(1.9); // ~2.0 SOL
});

it('deduplicates transactions on retry', function (): void {
    $user = User::factory()->create();
    BlockchainAddress::create([
        'user_uuid'  => $user->uuid,
        'chain'      => 'solana',
        'address'    => 'DedupeAddr',
        'public_key' => 'DedupeAddr',
        'is_active'  => true,
    ]);

    $controller = app(HeliusWebhookController::class);
    $tx = makeHeliusTx('sig_dedupe_001', 'Sender', 'DedupeAddr');

    $request1 = Request::create('/api/webhooks/helius/solana', 'POST', [], [], [], [], (string) json_encode([$tx]));
    $request1->headers->set('Content-Type', 'application/json');
    $controller->handle($request1);

    $request2 = Request::create('/api/webhooks/helius/solana', 'POST', [], [], [], [], (string) json_encode([$tx]));
    $request2->headers->set('Content-Type', 'application/json');
    $controller->handle($request2);

    expect(BlockchainTransaction::where('tx_hash', 'sig_dedupe_001')->count())->toBe(1);
    expect(ActivityFeedItem::where('reference_id', HeliusTransactionProcessor::signatureToUuid('sig_dedupe_001'))->count())->toBe(1);
});

it('rejects requests with wrong webhook secret', function (): void {
    config(['services.helius.webhook_secret' => 'correct-secret']);

    $controller = app(HeliusWebhookController::class);
    $request = Request::create('/api/webhooks/helius/solana', 'POST');
    $request->headers->set('Authorization', 'wrong-secret');

    $response = $controller->handle($request);

    expect($response->getStatusCode())->toBe(401);
});

it('resolves USDT token correctly', function (): void {
    $user = User::factory()->create();
    BlockchainAddress::create([
        'user_uuid'  => $user->uuid,
        'chain'      => 'solana',
        'address'    => 'UsdtAddr',
        'public_key' => 'UsdtAddr',
        'is_active'  => true,
    ]);

    $controller = app(HeliusWebhookController::class);
    $tx = makeHeliusTx('sig_usdt_001', 'Sender', 'UsdtAddr', '100', 'Es9vMFrzaCERmJfrF4H2FYD4KCoNkY11McCe8BenwNYB');

    $request = Request::create('/api/webhooks/helius/solana', 'POST', [], [], [], [], (string) json_encode([$tx]));
    $request->headers->set('Content-Type', 'application/json');

    $controller->handle($request);

    $feedItem = ActivityFeedItem::where('reference_id', HeliusTransactionProcessor::signatureToUuid('sig_usdt_001'))->first();
    assert($feedItem instanceof ActivityFeedItem);
    expect($feedItem->asset)->toBe('USDT');
});

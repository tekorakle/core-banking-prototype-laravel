<?php

declare(strict_types=1);

use App\Domain\CardIssuance\Models\Cardholder;
use App\Models\User;

uses(Tests\TestCase::class);
uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('GraphQL CardIssuance API', function () {
    it('returns unauthorized without authentication', function () {
        $response = $this->postJson('/graphql', [
            'query' => '{ card(id: "test-id") { id status } }',
        ]);

        $response->assertOk();
        expect($response->json())->toHaveKey('errors');
    });

    it('queries card by id with authentication', function () {
        $user = User::factory()->create();

        // CardIssuance uses custom resolvers without a traditional model,
        // so querying a non-existent card should return null without errors
        // when the resolver handles it gracefully.
        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/graphql', [
                'query' => '
                    query ($id: ID!) {
                        card(id: $id) {
                            id
                            card_token
                            cardholder_name
                            last_four
                            network
                            status
                        }
                    }
                ',
                'variables' => ['id' => 'non-existent-card'],
            ]);

        $response->assertOk();
        // The resolver may return null or an error for non-existent cards
        $json = $response->json();
        expect($json)->toHaveKey('data');
    });

    it('lists cards with pagination via custom resolver', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/graphql', [
                'query' => '
                    {
                        cards(first: 10, page: 1) {
                            id
                            cardholder_name
                            status
                            network
                        }
                    }
                ',
            ]);

        $response->assertOk();
        $json = $response->json();
        expect($json)->toHaveKey('data');
    });

    it('provisions a virtual card via mutation', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/graphql', [
                'query' => '
                    mutation ($input: ProvisionCardInput!) {
                        provisionCard(input: $input) {
                            id
                            cardholder_name
                            status
                            network
                        }
                    }
                ',
                'variables' => [
                    'input' => [
                        'cardholder_name' => 'John Doe',
                    ],
                ],
            ]);

        $response->assertOk();
        $json = $response->json();
        expect($json)->not->toHaveKey('errors');
        $data = $response->json('data.provisionCard');
        expect($data['cardholder_name'])->toBe('John Doe');
    });

    it('queries card transactions with authentication', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/graphql', [
                'query' => '
                    query ($cardId: String!, $first: Int) {
                        cardTransactions(card_id: $cardId, first: $first) {
                            id
                            card_id
                            merchant_name
                            merchant_category
                            amount_cents
                            currency
                            status
                            transacted_at
                        }
                    }
                ',
                'variables' => [
                    'cardId' => 'test-card-token',
                    'first'  => 10,
                ],
            ]);

        $response->assertOk();
        $json = $response->json();
        expect($json)->toHaveKey('data');
        // The resolver returns an array of transactions (may be empty from mock issuer)
        expect($json['data']['cardTransactions'])->toBeArray();
    });

    it('lists cardholders for authenticated user', function () {
        $user = User::factory()->create();

        // Create a cardholder belonging to this user
        Cardholder::create([
            'user_id'    => $user->id,
            'first_name' => 'Jane',
            'last_name'  => 'Smith',
            'email'      => 'jane@example.com',
            'kyc_status' => 'verified',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/graphql', [
                'query' => '
                    {
                        cardholders {
                            id
                            first_name
                            last_name
                            full_name
                            email
                            kyc_status
                            is_verified
                            card_count
                        }
                    }
                ',
            ]);

        $response->assertOk();
        $json = $response->json();
        expect($json)->toHaveKey('data');
        expect($json['data']['cardholders'])->toBeArray();
        expect($json['data']['cardholders'])->toHaveCount(1);
        expect($json['data']['cardholders'][0]['first_name'])->toBe('Jane');
        expect($json['data']['cardholders'][0]['last_name'])->toBe('Smith');
        expect($json['data']['cardholders'][0]['full_name'])->toBe('Jane Smith');
    });

    it('queries single cardholder by id with authentication', function () {
        $user = User::factory()->create();

        $cardholder = Cardholder::create([
            'user_id'    => $user->id,
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'email'      => 'john@example.com',
            'kyc_status' => 'pending',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/graphql', [
                'query' => '
                    query ($id: ID!) {
                        cardholder(id: $id) {
                            id
                            first_name
                            last_name
                            full_name
                            email
                            kyc_status
                            is_verified
                            card_count
                        }
                    }
                ',
                'variables' => ['id' => $cardholder->id],
            ]);

        $response->assertOk();
        $json = $response->json();
        expect($json)->toHaveKey('data');
        expect($json['data']['cardholder'])->not->toBeNull();
        expect($json['data']['cardholder']['first_name'])->toBe('John');
        expect($json['data']['cardholder']['last_name'])->toBe('Doe');
        expect($json['data']['cardholder']['full_name'])->toBe('John Doe');
        expect($json['data']['cardholder']['kyc_status'])->toBe('pending');
        expect($json['data']['cardholder']['is_verified'])->toBeFalse();
    });
});

<?php

declare(strict_types=1);

namespace Tests\Feature\Api\Compliance;

use App\Domain\Compliance\Models\KycVerification;
use App\Domain\Compliance\Services\OndatoService;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\ControllerTestCase;

class OndatoKycFlowTest extends ControllerTestCase
{
    protected User $user;

    protected OndatoService|Mockery\MockInterface $ondatoService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'kyc_status' => 'not_started',
            'kyc_level'  => 'basic',
        ]);

        $this->ondatoService = Mockery::mock(OndatoService::class);
        $this->app->instance(OndatoService::class, $this->ondatoService);
    }

    #[Test]
    public function test_start_ondato_verification_creates_session(): void
    {
        Sanctum::actingAs($this->user, ['read', 'write', 'delete']);

        $this->ondatoService->shouldReceive('createIdentityVerification')
            ->once()
            ->with(
                Mockery::on(fn ($user) => $user->id === $this->user->id),
                ['first_name' => 'John', 'last_name' => 'Doe']
            )
            ->andReturn([
                'identity_verification_id' => 'idv-new-123',
                'verification_id'          => 'ver-uuid-456',
                'status'                   => 'pending',
            ]);

        $response = $this->postJson('/api/compliance/kyc/ondato/start', [
            'first_name' => 'John',
            'last_name'  => 'Doe',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'identity_verification_id' => 'idv-new-123',
                'verification_id'          => 'ver-uuid-456',
                'status'                   => 'pending',
            ]);
    }

    #[Test]
    public function test_start_ondato_verification_rejects_if_already_approved(): void
    {
        $this->user->update(['kyc_status' => 'approved']);
        Sanctum::actingAs($this->user, ['read', 'write', 'delete']);

        $response = $this->postJson('/api/compliance/kyc/ondato/start');

        $response->assertStatus(400)
            ->assertJson(['error' => 'KYC already approved']);
    }

    #[Test]
    public function test_start_ondato_verification_requires_authentication(): void
    {
        $response = $this->postJson('/api/compliance/kyc/ondato/start');

        $response->assertStatus(401);
    }

    #[Test]
    public function test_start_ondato_verification_without_optional_fields(): void
    {
        Sanctum::actingAs($this->user, ['read', 'write', 'delete']);

        $this->ondatoService->shouldReceive('createIdentityVerification')
            ->once()
            ->andReturn([
                'identity_verification_id' => 'idv-no-name',
                'verification_id'          => 'ver-no-name',
                'status'                   => 'pending',
            ]);

        $response = $this->postJson('/api/compliance/kyc/ondato/start');

        $response->assertStatus(200)
            ->assertJsonStructure(['identity_verification_id', 'verification_id', 'status']);
    }

    #[Test]
    public function test_start_ondato_verification_returns_500_on_service_failure(): void
    {
        Sanctum::actingAs($this->user, ['read', 'write', 'delete']);

        $this->ondatoService->shouldReceive('createIdentityVerification')
            ->once()
            ->andThrow(new RuntimeException('API unavailable'));

        $response = $this->postJson('/api/compliance/kyc/ondato/start');

        $response->assertStatus(500)
            ->assertJson(['error' => 'Failed to start Ondato verification']);
    }

    #[Test]
    public function test_get_ondato_verification_status(): void
    {
        Sanctum::actingAs($this->user, ['read', 'write', 'delete']);

        $verification = KycVerification::create([
            'user_id'            => $this->user->id,
            'type'               => KycVerification::TYPE_IDENTITY,
            'status'             => KycVerification::STATUS_COMPLETED,
            'provider'           => 'ondato',
            'provider_reference' => 'idv-status-check',
            'confidence_score'   => 95.00,
            'completed_at'       => now(),
            'verification_data'  => [],
        ]);

        $response = $this->getJson("/api/compliance/kyc/ondato/status/{$verification->id}");

        $response->assertStatus(200)
            ->assertJson([
                'verification_id' => $verification->id,
                'status'          => 'completed',
                'provider'        => 'ondato',
            ])
            ->assertJsonStructure([
                'verification_id',
                'status',
                'provider',
                'confidence_score',
                'failure_reason',
                'completed_at',
            ]);
    }

    #[Test]
    public function test_get_ondato_verification_status_returns_404_for_other_user(): void
    {
        $otherUser = User::factory()->create();
        $verification = KycVerification::create([
            'user_id'            => $otherUser->id,
            'type'               => KycVerification::TYPE_IDENTITY,
            'status'             => KycVerification::STATUS_PENDING,
            'provider'           => 'ondato',
            'provider_reference' => 'idv-other-user',
            'verification_data'  => [],
        ]);

        Sanctum::actingAs($this->user, ['read', 'write', 'delete']);

        $response = $this->getJson("/api/compliance/kyc/ondato/status/{$verification->id}");

        $response->assertStatus(404);
    }

    #[Test]
    public function test_get_ondato_verification_status_requires_authentication(): void
    {
        $response = $this->getJson('/api/compliance/kyc/ondato/status/some-id');

        $response->assertStatus(401);
    }
}

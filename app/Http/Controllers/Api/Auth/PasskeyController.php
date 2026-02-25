<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Domain\Mobile\Exceptions\BiometricBlockedException;
use App\Domain\Mobile\Models\MobileDevice;
use App\Domain\Mobile\Services\MobileDeviceService;
use App\Domain\Mobile\Services\PasskeyAuthenticationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\PasskeyAuthenticateRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PasskeyController extends Controller
{
    public function __construct(
        private readonly PasskeyAuthenticationService $passkeyService,
        private readonly MobileDeviceService $deviceService,
    ) {
    }

    /**
     * Generate a WebAuthn challenge for passkey authentication.
     *
     * Accepts either `device_id` (legacy) or `email` (standard WebAuthn flow).
     * When `email` is provided, returns allowCredentials for all passkey-enabled
     * devices registered to that user.
     *
     * POST /v1/auth/passkey/challenge
     *
     * @OA\Post(
     *     path="/api/v1/auth/passkey/challenge",
     *     operationId="passkeyChallenge",
     *     summary="Generate WebAuthn challenge",
     *     description="Generates a WebAuthn challenge for passkey authentication. Accepts device_id or email as identifier. This is a public endpoint.",
     *     tags={"WebAuthn"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         @OA\Property(property="device_id", type="string", description="Unique device identifier (optional if email provided)"),
     *         @OA\Property(property="email", type="string", format="email", description="User email (optional if device_id provided)")
     *     )),
     *     @OA\Response(response=200, description="Challenge generated", @OA\JsonContent(
     *         @OA\Property(property="success", type="boolean", example=true),
     *         @OA\Property(property="data", type="object",
     *             @OA\Property(property="challenge", type="string"),
     *             @OA\Property(property="rp_id", type="string"),
     *             @OA\Property(property="timeout", type="integer", example=60000),
     *             @OA\Property(property="expires_at", type="string", format="date-time"),
     *             @OA\Property(property="allow_credentials", type="array", @OA\Items(type="object",
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="type", type="string", example="public-key")
     *             ))
     *         )
     *     )),
     *     @OA\Response(response=400, description="No passkeys available"),
     *     @OA\Response(response=404, description="User or device not found"),
     *     @OA\Response(response=422, description="Validation error — provide device_id or email")
     * )
     */
    public function challenge(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => ['nullable', 'string'],
            'email'     => ['nullable', 'string', 'email'],
        ]);

        $deviceId = $request->input('device_id');
        $email = $request->input('email');

        if (! $deviceId && ! $email) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'VALIDATION_ERROR',
                    'message' => 'Either device_id or email is required.',
                ],
            ], 422);
        }

        // Legacy flow: look up by device_id directly
        if ($deviceId) {
            return $this->challengeByDevice($request, $deviceId);
        }

        // Standard WebAuthn flow: look up by email, return allowCredentials
        return $this->challengeByEmail($request, $email);
    }

    /**
     * Legacy challenge flow: single device lookup by device_id.
     */
    private function challengeByDevice(Request $request, string $deviceId): JsonResponse
    {
        $device = $this->deviceService->findByDeviceId($deviceId);

        if (! $device) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'DEVICE_NOT_FOUND',
                    'message' => 'Device not found.',
                ],
            ], 404);
        }

        if (! $device->passkey_enabled || $device->passkey_credential_id === null) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'PASSKEY_NOT_AVAILABLE',
                    'message' => 'Passkey authentication is not enabled for this device.',
                ],
            ], 400);
        }

        $challenge = $this->passkeyService->generateChallenge($device, $request->ip());

        return response()->json([
            'success' => true,
            'data'    => [
                'challenge'         => $challenge->challenge,
                'rp_id'             => config('app.url'),
                'timeout'           => 60000,
                'expires_at'        => $challenge->expires_at->toIso8601String(),
                'allow_credentials' => [
                    [
                        'id'   => $device->passkey_credential_id,
                        'type' => 'public-key',
                    ],
                ],
            ],
        ]);
    }

    /**
     * Standard WebAuthn challenge flow: look up user by email, return all registered credentials.
     */
    private function challengeByEmail(Request $request, string $email): JsonResponse
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            // Return generic error to avoid user enumeration
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'PASSKEY_NOT_AVAILABLE',
                    'message' => 'No passkeys available for this account.',
                ],
            ], 400);
        }

        $devices = MobileDevice::where('user_id', $user->id)
            ->where('passkey_enabled', true)
            ->whereNotNull('passkey_credential_id')
            ->get();

        if ($devices->isEmpty()) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'PASSKEY_NOT_AVAILABLE',
                    'message' => 'No passkeys available for this account.',
                ],
            ], 400);
        }

        // Generate challenge using the first device (challenge is user-scoped, not device-scoped)
        $challenge = $this->passkeyService->generateChallenge($devices->first(), $request->ip());

        $allowCredentials = $devices->map(fn (MobileDevice $d) => [
            'id'   => $d->passkey_credential_id,
            'type' => 'public-key',
        ])->values()->all();

        return response()->json([
            'success' => true,
            'data'    => [
                'challenge'         => $challenge->challenge,
                'rp_id'             => config('app.url'),
                'timeout'           => 60000,
                'expires_at'        => $challenge->expires_at->toIso8601String(),
                'allow_credentials' => $allowCredentials,
            ],
        ]);
    }

    /**
     * Verify a WebAuthn assertion and authenticate.
     *
     * POST /v1/auth/passkey/authenticate
     *
     * @OA\Post(
     *     path="/api/v1/auth/passkey/authenticate",
     *     operationId="passkeyAuthenticate",
     *     summary="Verify WebAuthn assertion and authenticate",
     *     description="Verifies a WebAuthn assertion response and returns an access token on success.",
     *     tags={"WebAuthn"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"device_id", "challenge", "credential_id", "authenticator_data", "client_data_json", "signature"},
     *         @OA\Property(property="device_id", type="string", description="Unique device identifier"),
     *         @OA\Property(property="challenge", type="string", description="The challenge string from the challenge endpoint"),
     *         @OA\Property(property="credential_id", type="string", description="WebAuthn credential ID"),
     *         @OA\Property(property="authenticator_data", type="string", description="Base64-encoded authenticator data"),
     *         @OA\Property(property="client_data_json", type="string", description="Base64-encoded client data JSON"),
     *         @OA\Property(property="signature", type="string", description="Base64-encoded assertion signature")
     *     )),
     *     @OA\Response(response=200, description="Authentication successful", @OA\JsonContent(
     *         @OA\Property(property="success", type="boolean", example=true),
     *         @OA\Property(property="data", type="object",
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="expires_in", type="integer", nullable=true, example=86400),
     *             @OA\Property(property="expires_at", type="string", format="date-time")
     *         )
     *     )),
     *     @OA\Response(response=401, description="Authentication failed"),
     *     @OA\Response(response=404, description="Device not found"),
     *     @OA\Response(response=429, description="Too many failed attempts, passkey blocked")
     * )
     */
    public function authenticate(PasskeyAuthenticateRequest $request): JsonResponse
    {
        $device = $this->deviceService->findByDeviceId($request->device_id);

        if (! $device) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'DEVICE_NOT_FOUND',
                    'message' => 'Device not found.',
                ],
            ], 404);
        }

        try {
            $result = $this->passkeyService->verifyAndAuthenticate(
                device: $device,
                challenge: $request->challenge,
                credentialId: $request->credential_id,
                authenticatorData: $request->authenticator_data,
                clientDataJSON: $request->client_data_json,
                signature: $request->signature,
                ipAddress: $request->ip(),
            );
        } catch (BiometricBlockedException $e) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'          => 'PASSKEY_BLOCKED',
                    'message'       => 'Too many failed attempts. Passkey authentication temporarily blocked.',
                    'blocked_until' => $e->blockedUntil->toIso8601String(),
                ],
            ], 429);
        }

        if (! $result) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'AUTHENTICATION_FAILED',
                    'message' => 'Passkey verification failed. Please try again.',
                ],
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'user'               => $device->user,
                'access_token'       => $result['access_token'],
                'refresh_token'      => $result['refresh_token'],
                'token_type'         => 'Bearer',
                'expires_in'         => config('sanctum.expiration') ? (int) config('sanctum.expiration') * 60 : null,
                'refresh_expires_in' => config('sanctum.refresh_token_expiration') ? (int) config('sanctum.refresh_token_expiration') * 60 : null,
                'expires_at'         => $result['expires_at']->toIso8601String(),
            ],
        ]);
    }

    /**
     * Register a new passkey credential for the authenticated user's device.
     *
     * POST /auth/passkey/register
     *
     * @OA\Post(
     *     path="/api/auth/passkey/register",
     *     operationId="passkeyRegister",
     *     summary="Register a new passkey credential",
     *     description="Registers a new WebAuthn passkey credential for the authenticated user's device.",
     *     tags={"WebAuthn"},
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"device_id", "credential_id", "public_key"},
     *         @OA\Property(property="device_id", type="string", description="Unique device identifier"),
     *         @OA\Property(property="credential_id", type="string", description="WebAuthn credential ID"),
     *         @OA\Property(property="public_key", type="string", description="Base64-encoded public key")
     *     )),
     *     @OA\Response(response=201, description="Passkey registered successfully", @OA\JsonContent(
     *         @OA\Property(property="success", type="boolean", example=true),
     *         @OA\Property(property="data", type="object")
     *     )),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Device does not belong to authenticated user"),
     *     @OA\Response(response=404, description="Device not found")
     * )
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'device_id'     => ['required', 'string'],
            'credential_id' => ['required', 'string'],
            'public_key'    => ['required', 'string'],
        ]);

        $device = $this->deviceService->findByDeviceId($request->device_id);

        if (! $device) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'DEVICE_NOT_FOUND',
                    'message' => 'Device not found. Register the device first.',
                ],
            ], 404);
        }

        // Ensure the device belongs to the authenticated user
        $user = $request->user();
        if (! $user || $device->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'UNAUTHORIZED',
                    'message' => 'Device does not belong to the authenticated user.',
                ],
            ], 403);
        }

        $result = $this->passkeyService->registerPasskey(
            $device,
            $request->credential_id,
            $request->public_key,
        );

        return response()->json([
            'success' => true,
            'data'    => $result,
        ], 201);
    }
}

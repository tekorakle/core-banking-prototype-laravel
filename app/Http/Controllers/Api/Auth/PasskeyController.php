<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Domain\Mobile\Exceptions\BiometricBlockedException;
use App\Domain\Mobile\Services\MobileDeviceService;
use App\Domain\Mobile\Services\PasskeyAuthenticationService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\DeviceIdRequest;
use App\Http\Requests\Mobile\PasskeyAuthenticateRequest;
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
     * POST /v1/auth/passkey/challenge
     */
    public function challenge(DeviceIdRequest $request): JsonResponse
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
                'challenge'     => $challenge->challenge,
                'credential_id' => $device->passkey_credential_id,
                'rp_id'         => config('app.url'),
                'timeout'       => 60000,
                'expires_at'    => $challenge->expires_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Verify a WebAuthn assertion and authenticate.
     *
     * POST /v1/auth/passkey/authenticate
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
                'access_token' => $result['token'],
                'token_type'   => 'Bearer',
                'expires_at'   => $result['expires_at']->toIso8601String(),
            ],
        ]);
    }

    /**
     * Register a new passkey credential for the authenticated user's device.
     *
     * POST /auth/passkey/register
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

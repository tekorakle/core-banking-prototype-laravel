<?php

declare(strict_types=1);

namespace App\Domain\Mobile\Services;

use App\Domain\Mobile\Models\BiometricChallenge;
use App\Domain\Mobile\Models\MobileDevice;
use App\Domain\Mobile\Models\MobileDeviceSession;
use App\Models\User;
use App\Traits\HasApiScopes;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Service for handling biometric (device-bound) authentication.
 *
 * Uses ECDSA P-256 signatures for secure device-bound authentication.
 * The mobile app stores the private key in the secure enclave (iOS) or
 * Android Keystore, and signs challenges sent by the server.
 */
class BiometricAuthenticationService
{
    use HasApiScopes;

    /**
     * Session duration in minutes.
     */
    private const SESSION_DURATION_MINUTES = 60;

    /**
     * Extended session duration for trusted devices.
     */
    private const TRUSTED_SESSION_DURATION_MINUTES = 480; // 8 hours

    /**
     * Enable biometric authentication for a device.
     */
    public function enableBiometric(MobileDevice $device, string $publicKey, ?string $keyId = null): bool
    {
        // Validate public key format (must be a valid ECDSA P-256 public key)
        if (! $this->validatePublicKeyFormat($publicKey)) {
            Log::warning('Invalid biometric public key format', [
                'device_id' => $device->id,
            ]);

            return false;
        }

        $device->enableBiometric($publicKey, $keyId ?? $this->generateKeyId());

        Log::info('Biometric authentication enabled', [
            'user_id'   => $device->user_id,
            'device_id' => $device->device_id,
        ]);

        return true;
    }

    /**
     * Disable biometric authentication for a device.
     */
    public function disableBiometric(MobileDevice $device): void
    {
        $device->disableBiometric();

        // Invalidate all biometric sessions
        MobileDeviceSession::where('mobile_device_id', $device->id)
            ->where('is_biometric_session', true)
            ->delete();

        Log::info('Biometric authentication disabled', [
            'user_id'   => $device->user_id,
            'device_id' => $device->device_id,
        ]);
    }

    /**
     * Create a new challenge for biometric verification.
     */
    public function createChallenge(MobileDevice $device, ?string $ipAddress = null): BiometricChallenge
    {
        // Invalidate any existing pending challenges
        BiometricChallenge::where('mobile_device_id', $device->id)
            ->pending()
            ->update(['status' => BiometricChallenge::STATUS_EXPIRED]);

        return BiometricChallenge::createForDevice($device, $ipAddress);
    }

    /**
     * Verify a biometric signature and create a session.
     *
     * @param MobileDevice $device The device attempting authentication
     * @param string $challenge The challenge that was signed
     * @param string $signature The signature from the device (base64 encoded DER)
     * @param string|null $ipAddress The IP address of the request
     * @return array{token: string, expires_at: \Carbon\Carbon}|null
     */
    public function verifyAndCreateSession(
        MobileDevice $device,
        string $challenge,
        string $signature,
        ?string $ipAddress = null
    ): ?array {
        // Check if device can use biometric
        if (! $device->canUseBiometric()) {
            Log::warning('Device cannot use biometric', [
                'device_id'         => $device->id,
                'is_blocked'        => $device->is_blocked,
                'biometric_enabled' => $device->biometric_enabled,
            ]);

            return null;
        }

        // Find the pending challenge
        $biometricChallenge = BiometricChallenge::where('mobile_device_id', $device->id)
            ->where('challenge', $challenge)
            ->pending()
            ->first();

        if (! $biometricChallenge) {
            Log::warning('Biometric challenge not found or expired', [
                'device_id' => $device->id,
            ]);

            return null;
        }

        // Verify the signature (biometric_public_key is guaranteed non-null by canUseBiometric() check above)
        /** @var string $publicKey */
        $publicKey = $device->biometric_public_key;
        if (! $this->verifySignature($challenge, $signature, $publicKey)) {
            $biometricChallenge->markAsFailed();

            Log::warning('Biometric signature verification failed', [
                'device_id'    => $device->id,
                'challenge_id' => $biometricChallenge->id,
            ]);

            return null;
        }

        // Wrap all database writes in a transaction for consistency
        try {
            return DB::transaction(function () use ($device, $biometricChallenge, $ipAddress) {
                // Mark challenge as verified
                $biometricChallenge->markAsVerified();

                // Create session
                $sessionDuration = $device->is_trusted
                    ? self::TRUSTED_SESSION_DURATION_MINUTES
                    : self::SESSION_DURATION_MINUTES;

                $session = MobileDeviceSession::create([
                    'mobile_device_id'     => $device->id,
                    'user_id'              => $device->user_id,
                    'session_token'        => MobileDeviceSession::generateToken(),
                    'ip_address'           => $ipAddress,
                    'last_activity_at'     => now(),
                    'expires_at'           => now()->addMinutes($sessionDuration),
                    'is_biometric_session' => true,
                ]);

                // Create API token for the user
                $user = $device->user;
                if (! $user) {
                    Log::error('User not found for device', ['device_id' => $device->id]);
                    throw new RuntimeException('User not found for device');
                }
                $plainToken = $this->createTokenWithScopes($user, 'mobile-biometric');

                // Update device activity
                $device->update(['last_active_at' => now()]);

                Log::info('Biometric authentication successful', [
                    'user_id'    => $device->user_id,
                    'device_id'  => $device->device_id,
                    'session_id' => $session->id,
                ]);

                return [
                    'token'      => $plainToken,
                    'expires_at' => $session->expires_at,
                    'session_id' => $session->id,
                ];
            });
        } catch (Throwable $e) {
            Log::error('Biometric session creation failed', [
                'device_id' => $device->id,
                'error'     => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Verify an ECDSA signature.
     */
    private function verifySignature(string $data, string $signature, string $publicKey): bool
    {
        try {
            // Decode base64 signature
            $signatureBytes = base64_decode($signature, true);
            if ($signatureBytes === false) {
                return false;
            }

            // Format public key for OpenSSL
            $pemPublicKey = $this->formatPublicKeyAsPem($publicKey);
            if ($pemPublicKey === null) {
                return false;
            }

            // Create OpenSSL key resource
            $keyResource = openssl_pkey_get_public($pemPublicKey);
            if ($keyResource === false) {
                Log::debug('Failed to parse public key', [
                    'error' => openssl_error_string(),
                ]);

                return false;
            }

            // Verify signature
            $result = openssl_verify(
                $data,
                $signatureBytes,
                $keyResource,
                OPENSSL_ALGO_SHA256
            );

            return $result === 1;
        } catch (Exception $e) {
            Log::error('Signature verification error', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Validate the format of a public key.
     */
    private function validatePublicKeyFormat(string $publicKey): bool
    {
        // Accept PEM format
        if (str_starts_with($publicKey, '-----BEGIN PUBLIC KEY-----')) {
            $pemKey = $publicKey;
        } else {
            // Try to decode as base64
            $decoded = base64_decode($publicKey, true);
            if ($decoded === false) {
                return false;
            }
            $pemKey = $this->formatPublicKeyAsPem($publicKey);
        }

        if ($pemKey === null) {
            return false;
        }

        // Try to parse with OpenSSL
        $keyResource = openssl_pkey_get_public($pemKey);
        if ($keyResource === false) {
            return false;
        }

        // Verify it's an EC key
        $details = openssl_pkey_get_details($keyResource);

        return isset($details['ec']);
    }

    /**
     * Format a public key as PEM.
     */
    private function formatPublicKeyAsPem(string $publicKey): ?string
    {
        // If already PEM format, return as-is
        if (str_starts_with($publicKey, '-----BEGIN PUBLIC KEY-----')) {
            return $publicKey;
        }

        // Assume base64 encoded DER
        $decoded = base64_decode($publicKey, true);
        if ($decoded === false) {
            return null;
        }

        // Wrap in PEM format
        return "-----BEGIN PUBLIC KEY-----\n" .
            chunk_split(base64_encode($decoded), 64, "\n") .
            '-----END PUBLIC KEY-----';
    }

    /**
     * Generate a unique key ID.
     */
    private function generateKeyId(): string
    {
        return 'key_' . bin2hex(random_bytes(8)) . '_' . time();
    }

    /**
     * Clean up expired challenges.
     */
    public function cleanupExpiredChallenges(): int
    {
        return BiometricChallenge::expired()
            ->update(['status' => BiometricChallenge::STATUS_EXPIRED]);
    }

    /**
     * Clean up expired sessions.
     */
    public function cleanupExpiredSessions(): int
    {
        return MobileDeviceSession::expired()->delete();
    }

    /**
     * Invalidate all sessions for a user.
     */
    public function invalidateUserSessions(User $user): int
    {
        return MobileDeviceSession::where('user_id', $user->id)->delete();
    }

    /**
     * Invalidate all sessions for a device.
     */
    public function invalidateDeviceSessions(MobileDevice $device): int
    {
        return MobileDeviceSession::where('mobile_device_id', $device->id)->delete();
    }
}

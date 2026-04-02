<?php

declare(strict_types=1);

namespace App\Domain\Wallet\Helpers;

/**
 * Real Solana address derivation using ed25519 key generation and Base58 encoding.
 *
 * Uses PHP sodium (libsodium) for ed25519 keypair generation and GMP for
 * Base58 encoding — both are built into PHP 8.4, zero new dependencies.
 */
class SolanaAddressHelper
{
    private const BASE58_ALPHABET = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

    /**
     * Derive a real Solana address (ed25519 public key, Base58-encoded) from a seed string.
     *
     * The seed is hashed to 32 bytes via SHA-256 (incorporating the Solana BIP44 path)
     * to ensure correct length for sodium_crypto_sign_seed_keypair().
     */
    public static function deriveAddress(string $seed): string
    {
        $seed32 = hash('sha256', $seed . ":m/44'/501'/0'/0'", binary: true);

        $keypair = sodium_crypto_sign_seed_keypair($seed32);
        $publicKey = sodium_crypto_sign_publickey($keypair);

        sodium_memzero($keypair);
        sodium_memzero($seed32);

        return self::base58Encode($publicKey);
    }

    /**
     * Base58 encode using GMP big-integer math (Bitcoin/Solana alphabet).
     *
     * Same algorithm as X402SolanaHsmSignerService::base58Encode() — centralised
     * here to eliminate duplication across 4 call sites.
     */
    public static function base58Encode(string $bytes): string
    {
        $alphabet = self::BASE58_ALPHABET;

        // Count leading zero bytes (each maps to a '1' prefix)
        $leadingZeros = 0;
        $len = strlen($bytes);
        while ($leadingZeros < $len && $bytes[$leadingZeros] === "\x00") {
            $leadingZeros++;
        }

        $num = gmp_import($bytes);
        $encoded = '';
        $base = gmp_init(58);
        $zero = gmp_init(0);

        while (gmp_cmp($num, $zero) > 0) {
            [$num, $remainder] = gmp_div_qr($num, $base);
            $encoded = $alphabet[gmp_intval($remainder)] . $encoded;
        }

        return str_repeat('1', $leadingZeros) . $encoded;
    }

    /**
     * Validate a Solana address (Base58-encoded, 32-44 characters).
     */
    public static function isValid(string $address): bool
    {
        return (bool) preg_match('/^[1-9A-HJ-NP-Za-km-z]{32,44}$/', $address);
    }
}

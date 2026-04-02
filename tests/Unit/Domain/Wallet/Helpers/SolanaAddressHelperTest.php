<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Wallet\Helpers;

use App\Domain\Wallet\Helpers\SolanaAddressHelper;
use PHPUnit\Framework\Attributes\Test;

class SolanaAddressHelperTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function derives_valid_solana_address(): void
    {
        $address = SolanaAddressHelper::deriveAddress('test-seed');

        $this->assertMatchesRegularExpression(
            '/^[1-9A-HJ-NP-Za-km-z]{32,44}$/',
            $address,
        );
    }

    #[Test]
    public function deterministic_same_seed_same_address(): void
    {
        $a = SolanaAddressHelper::deriveAddress('user:42');
        $b = SolanaAddressHelper::deriveAddress('user:42');

        $this->assertSame($a, $b);
    }

    #[Test]
    public function different_seeds_different_addresses(): void
    {
        $a = SolanaAddressHelper::deriveAddress('user:1');
        $b = SolanaAddressHelper::deriveAddress('user:2');

        $this->assertNotSame($a, $b);
    }

    #[Test]
    public function base58_encode_handles_leading_zero_bytes(): void
    {
        // Two leading zero bytes → two '1' prefixes
        $bytes = "\x00\x00" . random_bytes(30);
        $encoded = SolanaAddressHelper::base58Encode($bytes);

        $this->assertStringStartsWith('11', $encoded);
    }

    #[Test]
    public function is_valid_accepts_real_derived_address(): void
    {
        $address = SolanaAddressHelper::deriveAddress('validation-test');

        $this->assertTrue(SolanaAddressHelper::isValid($address));
    }

    #[Test]
    public function is_valid_rejects_ethereum_address(): void
    {
        $this->assertFalse(SolanaAddressHelper::isValid('0x1234567890abcdef1234567890abcdef12345678'));
    }

    #[Test]
    public function is_valid_rejects_empty_string(): void
    {
        $this->assertFalse(SolanaAddressHelper::isValid(''));
    }

    #[Test]
    public function derived_address_is_correct_length(): void
    {
        // ed25519 public key is 32 bytes → Base58 encoded should be 43-44 chars
        $address = SolanaAddressHelper::deriveAddress('length-test');

        $this->assertGreaterThanOrEqual(32, strlen($address));
        $this->assertLessThanOrEqual(44, strlen($address));
    }
}

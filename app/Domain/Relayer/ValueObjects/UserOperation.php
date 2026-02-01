<?php

declare(strict_types=1);

namespace App\Domain\Relayer\ValueObjects;

/**
 * ERC-4337 UserOperation structure.
 *
 * This represents a transaction that can be sponsored by a paymaster,
 * allowing users to execute transactions without holding native gas tokens.
 */
final readonly class UserOperation
{
    public function __construct(
        public string $sender,
        public int $nonce,
        public string $initCode,
        public string $callData,
        public int $callGasLimit,
        public int $verificationGasLimit,
        public int $preVerificationGas,
        public int $maxFeePerGas,
        public int $maxPriorityFeePerGas,
        public string $paymasterAndData,
        public string $signature,
    ) {
    }

    /**
     * Create an unsigned UserOperation (for estimation).
     */
    public static function createUnsigned(
        string $sender,
        int $nonce,
        string $callData,
    ): self {
        return new self(
            sender: $sender,
            nonce: $nonce,
            initCode: '0x',
            callData: $callData,
            callGasLimit: 0,
            verificationGasLimit: 0,
            preVerificationGas: 0,
            maxFeePerGas: 0,
            maxPriorityFeePerGas: 0,
            paymasterAndData: '0x',
            signature: '0x',
        );
    }

    /**
     * Create a signed UserOperation with gas parameters.
     */
    public function withGasAndSignature(
        int $callGasLimit,
        int $verificationGasLimit,
        int $preVerificationGas,
        int $maxFeePerGas,
        int $maxPriorityFeePerGas,
        string $paymasterAndData,
        string $signature
    ): self {
        return new self(
            sender: $this->sender,
            nonce: $this->nonce,
            initCode: $this->initCode,
            callData: $this->callData,
            callGasLimit: $callGasLimit,
            verificationGasLimit: $verificationGasLimit,
            preVerificationGas: $preVerificationGas,
            maxFeePerGas: $maxFeePerGas,
            maxPriorityFeePerGas: $maxPriorityFeePerGas,
            paymasterAndData: $paymasterAndData,
            signature: $signature,
        );
    }

    /**
     * Convert to array for JSON-RPC calls.
     */
    public function toArray(): array
    {
        return [
            'sender' => $this->sender,
            'nonce' => '0x' . dechex($this->nonce),
            'initCode' => $this->initCode,
            'callData' => $this->callData,
            'callGasLimit' => '0x' . dechex($this->callGasLimit),
            'verificationGasLimit' => '0x' . dechex($this->verificationGasLimit),
            'preVerificationGas' => '0x' . dechex($this->preVerificationGas),
            'maxFeePerGas' => '0x' . dechex($this->maxFeePerGas),
            'maxPriorityFeePerGas' => '0x' . dechex($this->maxPriorityFeePerGas),
            'paymasterAndData' => $this->paymasterAndData,
            'signature' => $this->signature,
        ];
    }
}

# FinAegis Mobile Wallet - Technical Specification

**Version**: 1.0
**Date**: February 2026
**Status**: Draft for Review
**Target Release**: v2.4.0 (Q3 2026)

---

## Executive Summary

FinAegis Mobile is a next-generation embedded wallet application combining traditional banking convenience with blockchain-native privacy and compliance features. Inspired by Privy and Turnkey, it provides non-custodial key management with enterprise-grade security.

### Unique Value Propositions

| Feature | Description | Differentiator |
|---------|-------------|----------------|
| **Stablecoin Commerce** | Pay at physical/online shops with stablecoins | Fiat-like UX with crypto rails |
| **Privacy Layer** | Untraceable public transactions with fraud investigation capability | RAILGUN-inspired Proof of Innocence |
| **TrustCert Attestations** | Blockchain-verified enhanced KYC certificates | Immutable, expirable, verifiable credentials |

---

## 1. Product Vision

### 1.1 Target Users

| Persona | Use Case | Key Needs |
|---------|----------|-----------|
| **Retail Consumer** | Daily payments, savings | Simple UX, low fees, privacy |
| **Business Owner** | Accept crypto payments | POS integration, instant settlement |
| **High-Net-Worth Individual** | Asset management | Privacy, multi-sig, hardware wallet |
| **Enterprise/Government Vendor** | Dual-use goods trade | Enhanced verification, audit trail |

### 1.2 Core Principles

1. **Self-Custody First**: Users control their keys (Shamir sharding)
2. **Privacy by Default**: Transactions private unless disclosure required
3. **Compliance Ready**: Proof of Innocence, not surveillance
4. **Regulatory Friendly**: Works with institutions, not against them

---

## 2. Feature Specifications

### 2.1 Stablecoin Commerce

#### 2.1.1 Overview

Enable users to pay at participating merchants using stablecoins (USDC, USDT, DAI, or FinAegis-issued stablecoins) with a UX identical to traditional card payments.

#### 2.1.2 Payment Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                    STABLECOIN PAYMENT FLOW                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  1. SCAN                    2. CONFIRM                           │
│  ┌─────────────┐           ┌─────────────────────┐              │
│  │   ┌───┐     │           │  Pay €45.00         │              │
│  │   │QR │     │    →      │  to: Coffee Shop    │              │
│  │   └───┘     │           │  ───────────────    │              │
│  │  Scan Code  │           │  [USDC] €45.00      │              │
│  └─────────────┘           │  Fee: €0.02         │              │
│                            │  ═══════════════    │              │
│                            │  [👆 Pay with Face] │              │
│                            └─────────────────────┘              │
│                                                                  │
│  3. SIGN (Privacy Layer)   4. CONFIRM                           │
│  ┌─────────────────────┐   ┌─────────────────────┐              │
│  │  🔒 Private Tx       │   │  ✓ Payment Sent     │              │
│  │  Shielding...       │   │                     │              │
│  │  ████████░░ 80%     │   │  Ref: FA-2026-XXXX  │              │
│  └─────────────────────┘   │  [View Receipt]     │              │
│                            └─────────────────────┘              │
└─────────────────────────────────────────────────────────────────┘
```

#### 2.1.3 Technical Components

| Component | Implementation | Status |
|-----------|---------------|--------|
| **QR Code Standard** | EIP-681 / BIP-21 extended | New |
| **Payment Protocol** | EIP-712 typed signatures | New |
| **Stablecoin Support** | USDC, USDT, DAI, FA-USD | Existing + New |
| **Gas Abstraction** | EIP-4337 Account Abstraction | New |
| **Fiat Conversion** | Real-time oracle pricing | Existing |
| **Merchant SDK** | TypeScript/React Native | New |

#### 2.1.4 Merchant Integration

```typescript
// Merchant SDK - Payment Request
interface PaymentRequest {
  merchantId: string;           // FinAegis merchant ID
  amount: string;               // Amount in fiat (e.g., "45.00")
  currency: 'EUR' | 'USD' | 'GBP';
  acceptedTokens: string[];     // ['USDC', 'USDT', 'DAI']
  callbackUrl: string;          // Webhook for confirmation
  metadata: {
    orderId: string;
    description: string;
  };
}

// QR Code Payload
interface QRPayload {
  protocol: 'finaegis';
  version: 1;
  request: PaymentRequest;
  signature: string;            // Merchant signature
  expiresAt: number;            // Unix timestamp
}
```

#### 2.1.5 Settlement Options

| Mode | Speed | Fee | Use Case |
|------|-------|-----|----------|
| **Instant (L2)** | <2 seconds | 0.1% | Small purchases |
| **Batched (L1)** | ~15 minutes | 0.05% | Large settlements |
| **Privacy Shield** | ~30 seconds | 0.3% | Privacy-required |

---

### 2.2 Privacy Layer

#### 2.2.1 Overview

Implement a privacy system where:
- **Public**: Transactions are unlinkable (no address correlation)
- **Private**: Full audit trail for authorized fraud investigations
- **Compliant**: Proof of Innocence without revealing transaction history

#### 2.2.2 Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                     PRIVACY LAYER ARCHITECTURE                   │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────────┐    ┌──────────────────┐                   │
│  │   USER WALLET    │    │  SHIELD POOL     │                   │
│  │  ┌────────────┐  │    │  ┌────────────┐  │                   │
│  │  │ 100 USDC   │──┼────┼─▶│ Encrypted  │  │                   │
│  │  │ (visible)  │  │    │  │   UTXOs    │  │                   │
│  │  └────────────┘  │    │  └────────────┘  │                   │
│  └──────────────────┘    └────────┬─────────┘                   │
│                                   │                              │
│           ┌───────────────────────┼───────────────────────┐     │
│           │                       ▼                       │     │
│           │  ┌─────────────────────────────────────────┐ │     │
│           │  │         ZK-SNARK PROVER                 │ │     │
│           │  │  ┌─────────────────────────────────┐    │ │     │
│           │  │  │ Proof: "I own 50 USDC in pool"  │    │ │     │
│           │  │  │ WITHOUT revealing:               │    │ │     │
│           │  │  │  - Source address               │    │ │     │
│           │  │  │  - Transaction history          │    │ │     │
│           │  │  │  - Total balance                │    │ │     │
│           │  │  └─────────────────────────────────┘    │ │     │
│           │  └─────────────────────────────────────────┘ │     │
│           │                       │                       │     │
│           │              PRIVACY LAYER                    │     │
│           └───────────────────────┼───────────────────────┘     │
│                                   ▼                              │
│  ┌──────────────────┐    ┌──────────────────┐                   │
│  │   RECIPIENT      │    │  AUDIT VAULT     │                   │
│  │  ┌────────────┐  │    │  ┌────────────┐  │                   │
│  │  │ 50 USDC    │◀─┼────┼──│ Encrypted  │  │                   │
│  │  │ (received) │  │    │  │   Logs     │  │                   │
│  │  └────────────┘  │    │  └────────────┘  │                   │
│  └──────────────────┘    └──────────────────┘                   │
│                                   │                              │
│                          DECRYPT ONLY WITH:                      │
│                          - Court order                           │
│                          - Multi-sig (3-of-5 compliance)         │
│                          - User consent                          │
└─────────────────────────────────────────────────────────────────┘
```

#### 2.2.3 Privacy Modes

| Mode | Public Visibility | Audit Access | Use Case |
|------|-------------------|--------------|----------|
| **Transparent** | Full | Full | Regulatory reporting |
| **Shielded** | None (ZK proof only) | Encrypted logs | Personal privacy |
| **Selective** | Chosen fields only | Partial | Business compliance |

#### 2.2.4 Proof of Innocence

Users can generate cryptographic proofs that their funds:
- Do NOT originate from sanctioned addresses (OFAC, EU, UN)
- Were NOT involved in known hacks/exploits
- Meet specific compliance criteria

```typescript
interface ProofOfInnocence {
  proofType: 'SANCTIONS' | 'ORIGIN' | 'COMPLIANCE';
  generatedAt: Date;
  expiresAt: Date;
  proof: string;              // ZK-SNARK proof
  publicInputs: {
    sanctionsListHash: string;
    complianceLevel: 'BASIC' | 'ENHANCED' | 'FULL';
  };
  // Verifiable on-chain or off-chain
}
```

#### 2.2.5 Audit Vault Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                       AUDIT VAULT                                │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ENCRYPTION: AES-256-GCM + Shamir's Secret Sharing (5 shares)   │
│                                                                  │
│  KEY HOLDERS (3-of-5 required):                                  │
│  ├── FinAegis Compliance Officer                                │
│  ├── External Auditor (Big 4)                                   │
│  ├── Legal Counsel                                               │
│  ├── Regulatory Body Representative                              │
│  └── User Recovery Key (optional)                                │
│                                                                  │
│  STORED DATA (encrypted):                                        │
│  ├── Transaction ID                                              │
│  ├── Sender address                                              │
│  ├── Recipient address                                           │
│  ├── Amount                                                      │
│  ├── Timestamp                                                   │
│  ├── IP address (hashed)                                         │
│  └── Device fingerprint                                          │
│                                                                  │
│  ACCESS TRIGGERS:                                                │
│  ├── Court order with case number                                │
│  ├── Regulatory investigation (documented)                       │
│  ├── User-initiated disclosure                                   │
│  └── Fraud threshold exceeded (automatic flag)                   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

### 2.3 TrustCert - Enhanced KYC Attestations

#### 2.3.1 Overview

TrustCert is a blockchain-based certificate system that provides:
- **Enhanced Verification**: Beyond standard KYC (source of funds, beneficial ownership, etc.)
- **Immutable Proof**: On-chain attestation that cannot be falsified
- **Expirable**: Certificates have validity periods
- **Verifiable**: Anyone can verify without accessing underlying data

#### 2.3.2 Use Cases

| Certificate Type | Verification Level | Validity | Use Case |
|-----------------|-------------------|----------|----------|
| **Personal Trust** | Enhanced KYC | 1 year | High-value transactions |
| **Business Trust** | Full KYB + Beneficial Ownership | 2 years | B2B transactions |
| **Dual-Use Export** | Enhanced + Government checks | 6 months | Controlled goods trade |
| **Accredited Investor** | Financial verification | 1 year | Investment access |
| **White Hat** | Technical + Background check | 1 year | Security research |

#### 2.3.3 Certificate Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                    TRUSTCERT ARCHITECTURE                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                  ON-CHAIN (Soulbound Token)              │    │
│  │  ┌─────────────────────────────────────────────────┐    │    │
│  │  │  Token ID: 0x1234...                            │    │    │
│  │  │  Owner: 0xUserWallet...                         │    │    │
│  │  │  Type: BUSINESS_TRUST                           │    │    │
│  │  │  Issuer: 0xFinAegis...                          │    │    │
│  │  │  IssuedAt: 2026-02-01                           │    │    │
│  │  │  ExpiresAt: 2028-02-01                          │    │    │
│  │  │  CredentialHash: 0xabcd...                      │    │    │
│  │  │  Status: ACTIVE                                 │    │    │
│  │  │  Revocable: true                                │    │    │
│  │  └─────────────────────────────────────────────────┘    │    │
│  │                         │                                │    │
│  │                         │ Verifiable Credential          │    │
│  │                         ▼                                │    │
│  └─────────────────────────────────────────────────────────┘    │
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                  OFF-CHAIN (Encrypted Storage)           │    │
│  │  ┌─────────────────────────────────────────────────┐    │    │
│  │  │  Full Name: [ENCRYPTED]                         │    │    │
│  │  │  Company: [ENCRYPTED]                           │    │    │
│  │  │  Verification Documents: [ENCRYPTED]            │    │    │
│  │  │  Beneficial Owners: [ENCRYPTED]                 │    │    │
│  │  │  Source of Funds: [ENCRYPTED]                   │    │    │
│  │  │  Background Check: [ENCRYPTED]                  │    │    │
│  │  └─────────────────────────────────────────────────┘    │    │
│  │                                                          │    │
│  │  Decryption: Requires user consent + FinAegis key        │    │
│  └─────────────────────────────────────────────────────────┘    │
│                                                                  │
│  VERIFICATION FLOW:                                              │
│  1. Verifier requests proof                                      │
│  2. User generates ZK proof from SBT                             │
│  3. Proof confirms: "Valid BUSINESS_TRUST cert, not expired"     │
│  4. No PII disclosed                                             │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

#### 2.3.4 Smart Contract Interface

```solidity
// SPDX-License-Identifier: MIT
pragma solidity ^0.8.20;

interface ITrustCert {
    enum CertType {
        PERSONAL_TRUST,
        BUSINESS_TRUST,
        DUAL_USE_EXPORT,
        ACCREDITED_INVESTOR,
        WHITE_HAT
    }

    enum Status { PENDING, ACTIVE, SUSPENDED, REVOKED, EXPIRED }

    struct Certificate {
        uint256 tokenId;
        address holder;
        CertType certType;
        uint256 issuedAt;
        uint256 expiresAt;
        bytes32 credentialHash;    // Hash of off-chain data
        Status status;
        string metadataURI;        // IPFS/Arweave link
    }

    // Issue certificate (only authorized issuer)
    function issue(
        address to,
        CertType certType,
        uint256 validityDays,
        bytes32 credentialHash
    ) external returns (uint256 tokenId);

    // Revoke certificate (issuer or holder)
    function revoke(uint256 tokenId, string calldata reason) external;

    // Verify certificate validity
    function verify(uint256 tokenId) external view returns (
        bool isValid,
        CertType certType,
        uint256 expiresAt
    );

    // Generate ZK proof of certificate ownership
    function generateProof(
        uint256 tokenId,
        bytes calldata proofRequest
    ) external view returns (bytes memory proof);

    // Verify ZK proof (can be called by anyone)
    function verifyProof(
        bytes calldata proof,
        bytes calldata publicInputs
    ) external view returns (bool);

    // Soulbound: transfers are disabled
    function transferFrom(address, address, uint256) external pure {
        revert("TrustCert: Soulbound - transfers disabled");
    }
}
```

#### 2.3.5 Verification Process

```
┌─────────────────────────────────────────────────────────────────┐
│               TRUSTCERT ISSUANCE WORKFLOW                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  STEP 1: APPLICATION                                             │
│  ├── User selects certificate type                               │
│  ├── Uploads required documents                                  │
│  ├── Pays verification fee (crypto/fiat)                         │
│  └── Signs consent for background check                          │
│                                                                  │
│  STEP 2: VERIFICATION (7-14 days)                                │
│  ├── Document verification (AI + Human review)                   │
│  ├── Identity verification (Liveness + Document match)           │
│  ├── Background checks (PEP, Sanctions, Criminal)                │
│  ├── Source of funds verification                                │
│  └── Beneficial ownership discovery                              │
│                                                                  │
│  STEP 3: ENHANCED CHECKS (for specific types)                    │
│  ├── DUAL_USE_EXPORT: Government database check                  │
│  ├── ACCREDITED_INVESTOR: Financial verification                 │
│  ├── WHITE_HAT: Technical assessment + references                │
│  └── BUSINESS_TRUST: Company registry + UBO verification         │
│                                                                  │
│  STEP 4: ISSUANCE                                                │
│  ├── Generate credential hash                                    │
│  ├── Mint Soulbound Token to user wallet                         │
│  ├── Store encrypted data off-chain                              │
│  └── Emit CertificateIssued event                                │
│                                                                  │
│  STEP 5: ONGOING MONITORING                                      │
│  ├── Continuous sanctions screening                              │
│  ├── Adverse media monitoring                                    │
│  ├── Renewal reminders (30, 14, 7 days before expiry)            │
│  └── Auto-expiration at validity end                             │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 3. Technical Architecture

### 3.1 Mobile App Stack

```
┌─────────────────────────────────────────────────────────────────┐
│                    MOBILE APP ARCHITECTURE                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                    PRESENTATION LAYER                    │    │
│  │  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐    │    │
│  │  │  Home   │  │ Wallet  │  │   Pay   │  │ Profile │    │    │
│  │  └─────────┘  └─────────┘  └─────────┘  └─────────┘    │    │
│  │                                                          │    │
│  │  Framework: Expo SDK 52 (React Native)                   │    │
│  │  UI: NativeWind (Tailwind CSS)                           │    │
│  │  Navigation: Expo Router (file-based)                    │    │
│  │  Animation: Reanimated 3                                 │    │
│  └─────────────────────────────────────────────────────────┘    │
│                              │                                   │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                    STATE MANAGEMENT                       │    │
│  │  ┌─────────────────┐  ┌─────────────────────────────┐   │    │
│  │  │     Zustand     │  │     TanStack Query          │   │    │
│  │  │  (Local State)  │  │  (Server State + Cache)     │   │    │
│  │  └─────────────────┘  └─────────────────────────────┘   │    │
│  └─────────────────────────────────────────────────────────┘    │
│                              │                                   │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                    SECURITY LAYER                         │    │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐      │    │
│  │  │   Passkeys  │  │  Biometric  │  │   Secure    │      │    │
│  │  │   (FIDO2)   │  │   (P-256)   │  │   Enclave   │      │    │
│  │  └─────────────┘  └─────────────┘  └─────────────┘      │    │
│  │                                                          │    │
│  │  Key Storage: expo-secure-store + Keychain/Keystore      │    │
│  └─────────────────────────────────────────────────────────┘    │
│                              │                                   │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                    WALLET ENGINE                          │    │
│  │  ┌─────────────────────────────────────────────────┐    │    │
│  │  │              KEY MANAGEMENT                       │    │    │
│  │  │  ┌───────────┐ ┌───────────┐ ┌───────────┐      │    │    │
│  │  │  │  Device   │ │   Auth    │ │ Recovery  │      │    │    │
│  │  │  │  Shard    │ │  Shard    │ │  Shard    │      │    │    │
│  │  │  │ (Enclave) │ │  (HSM)    │ │ (Cloud)   │      │    │    │
│  │  │  └───────────┘ └───────────┘ └───────────┘      │    │    │
│  │  │           Shamir's Secret Sharing (2-of-3)       │    │    │
│  │  └─────────────────────────────────────────────────┘    │    │
│  │                                                          │    │
│  │  ┌─────────────────────────────────────────────────┐    │    │
│  │  │              BLOCKCHAIN CLIENTS                   │    │    │
│  │  │  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌───────┐ │    │    │
│  │  │  │Ethereum │ │ Polygon │ │   BSC   │ │Bitcoin│ │    │    │
│  │  │  │ (ethers)│ │ (ethers)│ │ (ethers)│ │(bitcoinjs)│  │    │
│  │  │  └─────────┘ └─────────┘ └─────────┘ └───────┘ │    │    │
│  │  └─────────────────────────────────────────────────┘    │    │
│  │                                                          │    │
│  │  ┌─────────────────────────────────────────────────┐    │    │
│  │  │              PRIVACY ENGINE                       │    │    │
│  │  │  ┌─────────────────┐  ┌─────────────────┐       │    │    │
│  │  │  │   ZK Prover     │  │  Shield Pool    │       │    │    │
│  │  │  │  (snarkjs/wasm) │  │   Interface     │       │    │    │
│  │  │  └─────────────────┘  └─────────────────┘       │    │    │
│  │  └─────────────────────────────────────────────────┘    │    │
│  └─────────────────────────────────────────────────────────┘    │
│                              │                                   │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │                    NETWORK LAYER                          │    │
│  │  ┌─────────────────┐  ┌─────────────────────────────┐   │    │
│  │  │   REST API      │  │     WebSocket (Soketi)      │   │    │
│  │  │   (Axios)       │  │   (Real-time updates)       │   │    │
│  │  └─────────────────┘  └─────────────────────────────┘   │    │
│  └─────────────────────────────────────────────────────────┘    │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 3.2 Key Management (Shamir's Secret Sharing)

```typescript
// Key Sharding Implementation
interface KeyShardingConfig {
  totalShards: 3;
  threshold: 2;        // 2-of-3 required
  algorithm: 'shamir';
  encryptionCurve: 'secp256k1';
}

interface KeyShards {
  deviceShard: {
    storage: 'secure-enclave';     // iOS Keychain / Android Keystore
    encryption: 'AES-256-GCM';
    biometricProtected: true;
  };
  authShard: {
    storage: 'backend-hsm';        // FinAegis HSM
    retrieval: 'authenticated-api';
    sessionBound: true;
  };
  recoveryShard: {
    storage: 'encrypted-cloud';    // User's iCloud/Google Drive
    encryption: 'user-password-derived';
    optional: true;
  };
}

// Signing Flow
async function signTransaction(tx: Transaction): Promise<SignedTransaction> {
  // 1. Get device shard (biometric auth required)
  const deviceShard = await secureEnclave.getShard({
    biometric: true,
    reason: 'Authorize transaction'
  });

  // 2. Get auth shard from backend
  const authShard = await api.getAuthShard({
    sessionToken: currentSession.token,
    transactionHash: tx.hash
  });

  // 3. Reconstruct key in memory (never persisted)
  const privateKey = shamirs.combine([deviceShard, authShard]);

  // 4. Sign transaction
  const signature = await sign(tx, privateKey);

  // 5. Immediately clear key from memory
  privateKey.fill(0);

  return { tx, signature };
}
```

### 3.3 Privacy Layer Integration

```typescript
// Privacy Transaction Flow
interface PrivacyTransaction {
  type: 'SHIELD' | 'UNSHIELD' | 'TRANSFER';
  amount: string;
  token: string;
  recipient?: string;        // Only for TRANSFER/UNSHIELD
  privacyLevel: 'FULL' | 'SELECTIVE';
  auditConsent: boolean;     // Required for compliance
}

async function executePrivacyTransaction(
  tx: PrivacyTransaction
): Promise<PrivacyTransactionResult> {
  // 1. Generate ZK proof for transaction validity
  const proof = await zkProver.generateProof({
    type: tx.type,
    amount: tx.amount,
    publicInputs: {
      token: tx.token,
      shieldPoolAddress: SHIELD_POOL_ADDRESS,
    },
    privateInputs: {
      balance: await getShieldedBalance(),
      nullifier: generateNullifier(),
    }
  });

  // 2. Create audit log (encrypted)
  const auditLog = await createAuditLog({
    transaction: tx,
    proof: proof.publicSignals,
    timestamp: Date.now(),
    deviceId: deviceId,
  });

  // 3. Submit to privacy pool
  const result = await privacyPool.execute({
    proof: proof.proof,
    publicSignals: proof.publicSignals,
    auditLogHash: auditLog.hash,
  });

  return result;
}
```

---

## 4. Screen Specifications

### 4.1 Screen Map

```
┌─────────────────────────────────────────────────────────────────┐
│                        SCREEN MAP                                │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ONBOARDING                                                      │
│  ├── Welcome                                                     │
│  ├── Create/Import Wallet                                        │
│  ├── Passkey Setup                                               │
│  ├── Biometric Setup                                             │
│  └── Recovery Setup (optional)                                   │
│                                                                  │
│  MAIN (Tab Navigation)                                           │
│  ├── Home                                                        │
│  │   ├── Balance Overview                                        │
│  │   ├── Quick Actions                                           │
│  │   ├── Recent Transactions                                     │
│  │   └── TrustCert Status                                        │
│  │                                                               │
│  ├── Wallet                                                      │
│  │   ├── Asset List                                              │
│  │   ├── Asset Detail                                            │
│  │   ├── Receive (QR)                                            │
│  │   └── Privacy Balance                                         │
│  │                                                               │
│  ├── Pay                                                         │
│  │   ├── Scan QR                                                 │
│  │   ├── Payment Confirm                                         │
│  │   ├── Send                                                    │
│  │   └── Request                                                 │
│  │                                                               │
│  ├── Activity                                                    │
│  │   ├── Transaction List                                        │
│  │   ├── Transaction Detail                                      │
│  │   ├── Filters                                                 │
│  │   └── Export                                                  │
│  │                                                               │
│  └── Profile                                                     │
│      ├── Account Settings                                        │
│      ├── Security Settings                                       │
│      ├── TrustCert Management                                    │
│      ├── Privacy Settings                                        │
│      ├── Connected Devices                                       │
│      └── Support                                                 │
│                                                                  │
│  MODALS/SHEETS                                                   │
│  ├── Transaction Signing                                         │
│  ├── Biometric Prompt                                            │
│  ├── Privacy Shield Progress                                     │
│  ├── Certificate Verification                                    │
│  └── Error/Success States                                        │
│                                                                  │
│  FLOWS                                                           │
│  ├── TrustCert Application                                       │
│  ├── Privacy Shield/Unshield                                     │
│  ├── Merchant Payment                                            │
│  ├── P2P Transfer                                                │
│  └── Account Recovery                                            │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 4.2 Key Screen Wireframes

#### 4.2.1 Home Screen

```
┌─────────────────────────────────────────┐
│ ≡                        FinAegis    🔔 │
├─────────────────────────────────────────┤
│                                         │
│  Good morning, Alice                    │
│                                         │
│  ┌─────────────────────────────────┐   │
│  │      Total Balance               │   │
│  │      $12,450.00                  │   │
│  │      ▲ +2.3% today               │   │
│  │                                  │   │
│  │  🔒 Shielded: $5,000.00          │   │
│  └─────────────────────────────────┘   │
│                                         │
│  ┌────────┐ ┌────────┐ ┌────────┐      │
│  │  Send  │ │  Pay   │ │Receive │      │
│  │   ↑    │ │   📱   │ │   ↓    │      │
│  └────────┘ └────────┘ └────────┘      │
│                                         │
│  TrustCert Status                       │
│  ┌─────────────────────────────────┐   │
│  │ ✓ Business Trust    Exp: 2028   │   │
│  │ ○ White Hat         [Apply →]   │   │
│  └─────────────────────────────────┘   │
│                                         │
│  Recent Activity                        │
│  ┌─────────────────────────────────┐   │
│  │ ↓ Coffee Shop      -$4.50  🔒   │   │
│  │ ↑ Salary Deposit   +$5,000     │   │
│  │ ↓ Amazon           -$125.00    │   │
│  │                   [View All →]  │   │
│  └─────────────────────────────────┘   │
│                                         │
├─────────────────────────────────────────┤
│  🏠     💰      📱      📊      👤     │
│ Home  Wallet   Pay   Activity Profile  │
└─────────────────────────────────────────┘
```

#### 4.2.2 Payment Screen

```
┌─────────────────────────────────────────┐
│ ←              Pay                    ✕ │
├─────────────────────────────────────────┤
│                                         │
│         ┌───────────────────┐          │
│         │                   │          │
│         │    📷 SCANNER     │          │
│         │                   │          │
│         │   Point camera    │          │
│         │   at QR code      │          │
│         │                   │          │
│         └───────────────────┘          │
│                                         │
│  ─────────── OR ───────────            │
│                                         │
│  ┌─────────────────────────────────┐   │
│  │  Enter address or username      │   │
│  └─────────────────────────────────┘   │
│                                         │
│  Recent Payments                        │
│  ┌─────────────────────────────────┐   │
│  │ ☕ Coffee Shop                   │   │
│  │ 🏪 Local Grocery                 │   │
│  │ 👤 @alice                        │   │
│  └─────────────────────────────────┘   │
│                                         │
│  ┌─────────────────────────────────┐   │
│  │      🔒 Privacy Mode: ON         │   │
│  │      Transactions are shielded   │   │
│  └─────────────────────────────────┘   │
│                                         │
├─────────────────────────────────────────┤
│  🏠     💰      📱      📊      👤     │
└─────────────────────────────────────────┘
```

#### 4.2.3 TrustCert Application

```
┌─────────────────────────────────────────┐
│ ←        TrustCert Application        ✕ │
├─────────────────────────────────────────┤
│                                         │
│  Apply for: Business Trust Certificate  │
│                                         │
│  ┌─────────────────────────────────┐   │
│  │  Step 2 of 5: Business Details   │   │
│  │  ████████████░░░░░░░░  40%       │   │
│  └─────────────────────────────────┘   │
│                                         │
│  Company Registration                   │
│  ┌─────────────────────────────────┐   │
│  │  Company Name                    │   │
│  │  ┌───────────────────────────┐  │   │
│  │  │ Acme Corporation          │  │   │
│  │  └───────────────────────────┘  │   │
│  │                                  │   │
│  │  Registration Number             │   │
│  │  ┌───────────────────────────┐  │   │
│  │  │ DE123456789               │  │   │
│  │  └───────────────────────────┘  │   │
│  │                                  │   │
│  │  Country of Incorporation        │   │
│  │  ┌───────────────────────────┐  │   │
│  │  │ Germany                 ▼ │  │   │
│  │  └───────────────────────────┘  │   │
│  └─────────────────────────────────┘   │
│                                         │
│  Upload Documents                       │
│  ┌─────────────────────────────────┐   │
│  │  📄 Certificate of Inc.  [Upload]│   │
│  │  📄 UBO Declaration      [Upload]│   │
│  │  📄 Financial Statements [Upload]│   │
│  └─────────────────────────────────┘   │
│                                         │
│  ┌─────────────────────────────────┐   │
│  │           Continue →             │   │
│  └─────────────────────────────────┘   │
│                                         │
└─────────────────────────────────────────┘
```

---

## 5. API Specifications

### 5.1 New Endpoints Required

#### 5.1.1 Stablecoin Commerce APIs

```yaml
# Payment Request
POST /api/commerce/payment-requests
Request:
  merchantId: string
  amount: string
  currency: string
  acceptedTokens: string[]
  orderId: string
  expiresIn: number (seconds)
Response:
  requestId: string
  qrCodeData: string
  deepLink: string
  expiresAt: datetime

# Execute Payment
POST /api/commerce/payments
Request:
  requestId: string
  fromAddress: string
  token: string
  signature: string
  privacyMode: boolean
Response:
  paymentId: string
  status: 'pending' | 'confirmed' | 'failed'
  txHash: string
  shieldedTxId?: string

# Get Payment Status
GET /api/commerce/payments/{paymentId}
Response:
  paymentId: string
  status: string
  confirmations: number
  merchantConfirmed: boolean
```

#### 5.1.2 Privacy Layer APIs

```yaml
# Shield Funds (Deposit to Privacy Pool)
POST /api/privacy/shield
Request:
  token: string
  amount: string
  proof: string (ZK proof of ownership)
Response:
  shieldId: string
  status: 'pending' | 'shielded'
  auditLogId: string

# Unshield Funds (Withdraw from Privacy Pool)
POST /api/privacy/unshield
Request:
  token: string
  amount: string
  recipient: string
  proof: string (ZK proof)
Response:
  unshieldId: string
  txHash: string
  status: string

# Private Transfer
POST /api/privacy/transfer
Request:
  token: string
  amount: string
  recipientViewingKey: string
  proof: string
Response:
  transferId: string
  status: string

# Get Shielded Balance
GET /api/privacy/balance
Response:
  balances:
    - token: string
      shieldedAmount: string
      pendingShield: string
      pendingUnshield: string

# Generate Proof of Innocence
POST /api/privacy/proof-of-innocence
Request:
  proofType: 'SANCTIONS' | 'ORIGIN' | 'COMPLIANCE'
  sanctionsListVersion: string
Response:
  proof: string
  publicInputs: object
  expiresAt: datetime
  verificationUrl: string
```

#### 5.1.3 TrustCert APIs

```yaml
# Start Certificate Application
POST /api/trustcert/applications
Request:
  certType: string
  applicantType: 'INDIVIDUAL' | 'BUSINESS'
Response:
  applicationId: string
  requiredDocuments: string[]
  estimatedDays: number
  fee: string

# Upload Document
POST /api/trustcert/applications/{id}/documents
Request:
  documentType: string
  file: binary
Response:
  documentId: string
  verificationStatus: 'pending' | 'verified' | 'rejected'

# Get Application Status
GET /api/trustcert/applications/{id}
Response:
  applicationId: string
  status: string
  currentStep: number
  totalSteps: number
  documents: array
  estimatedCompletion: datetime

# Get User Certificates
GET /api/trustcert/certificates
Response:
  certificates:
    - tokenId: string
      certType: string
      issuedAt: datetime
      expiresAt: datetime
      status: string
      onChainUrl: string

# Generate Verification Proof
POST /api/trustcert/certificates/{tokenId}/verify
Request:
  proofRequest: object (what to prove)
Response:
  proof: string
  publicInputs: object
  verifiablePresentation: string

# Revoke Certificate
DELETE /api/trustcert/certificates/{tokenId}
Request:
  reason: string
Response:
  revoked: boolean
  txHash: string
```

### 5.2 WebSocket Channels (New)

```yaml
# Privacy Pool Events
Channel: private-privacy.{userId}
Events:
  - shield.confirmed
  - unshield.confirmed
  - transfer.received
  - proof.generated

# Commerce Events
Channel: private-commerce.{merchantId}
Events:
  - payment.received
  - payment.confirmed
  - settlement.completed

# TrustCert Events
Channel: private-trustcert.{userId}
Events:
  - application.updated
  - document.verified
  - certificate.issued
  - certificate.expiring
```

---

## 6. Backend Upgrade Plan

### 6.1 New Domains Required

```
app/Domain/
├── Privacy/                    # NEW DOMAIN
│   ├── Models/
│   │   ├── ShieldedBalance.php
│   │   ├── ShieldTransaction.php
│   │   ├── PrivacyProof.php
│   │   └── AuditVaultEntry.php
│   ├── Services/
│   │   ├── PrivacyPoolService.php
│   │   ├── ZkProverService.php
│   │   ├── ProofOfInnocenceService.php
│   │   └── AuditVaultService.php
│   ├── Contracts/
│   │   └── PrivacyPoolInterface.php
│   └── Events/
│       ├── FundsShielded.php
│       ├── FundsUnshielded.php
│       └── PrivateTransferExecuted.php
│
├── Commerce/                   # NEW DOMAIN
│   ├── Models/
│   │   ├── Merchant.php
│   │   ├── PaymentRequest.php
│   │   ├── StablecoinPayment.php
│   │   └── Settlement.php
│   ├── Services/
│   │   ├── PaymentRequestService.php
│   │   ├── PaymentExecutionService.php
│   │   ├── SettlementService.php
│   │   └── MerchantOnboardingService.php
│   └── Events/
│       ├── PaymentReceived.php
│       └── SettlementCompleted.php
│
├── TrustCert/                  # NEW DOMAIN
│   ├── Models/
│   │   ├── Certificate.php
│   │   ├── CertificateApplication.php
│   │   ├── VerificationDocument.php
│   │   └── CertificateRevocation.php
│   ├── Services/
│   │   ├── CertificateIssuanceService.php
│   │   ├── VerificationService.php
│   │   ├── BlockchainMintService.php
│   │   └── ZkVerificationService.php
│   ├── Contracts/
│   │   └── CertificateVerifierInterface.php
│   └── SmartContracts/
│       └── TrustCertSBT.sol
│
└── KeyManagement/              # ENHANCED DOMAIN
    ├── Models/
    │   ├── KeyShard.php
    │   └── RecoveryBackup.php
    ├── Services/
    │   ├── ShamirService.php
    │   ├── KeyReconstructionService.php
    │   └── RecoveryService.php
    └── HSM/
        └── HsmIntegrationService.php
```

### 6.2 Database Migrations

```php
// 2026_02_XX_000001_create_privacy_tables.php
Schema::create('shielded_balances', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('user_id');
    $table->string('token_address');
    $table->string('commitment');           // Pedersen commitment
    $table->decimal('amount', 36, 18);
    $table->string('nullifier_hash')->unique();
    $table->timestamps();
});

Schema::create('shield_transactions', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('user_id');
    $table->enum('type', ['SHIELD', 'UNSHIELD', 'TRANSFER']);
    $table->string('token_address');
    $table->decimal('amount', 36, 18);
    $table->string('tx_hash')->nullable();
    $table->string('proof');
    $table->json('public_inputs');
    $table->enum('status', ['pending', 'confirmed', 'failed']);
    $table->timestamps();
});

Schema::create('audit_vault_entries', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('transaction_id');
    $table->text('encrypted_data');         // AES-256-GCM
    $table->string('encryption_key_id');    // Shamir shard reference
    $table->json('key_holders');            // Required signers
    $table->boolean('is_decrypted')->default(false);
    $table->timestamp('decrypted_at')->nullable();
    $table->string('decryption_reason')->nullable();
    $table->timestamps();
});

// 2026_02_XX_000002_create_commerce_tables.php
Schema::create('merchants', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('user_id');
    $table->string('business_name');
    $table->string('merchant_code')->unique();
    $table->json('accepted_tokens');
    $table->string('settlement_address');
    $table->enum('settlement_frequency', ['instant', 'daily', 'weekly']);
    $table->decimal('fee_rate', 5, 4);
    $table->boolean('is_verified')->default(false);
    $table->timestamps();
});

Schema::create('stablecoin_payments', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('merchant_id');
    $table->uuid('payer_id')->nullable();
    $table->string('order_id');
    $table->string('token_address');
    $table->decimal('amount', 36, 18);
    $table->decimal('fiat_amount', 18, 2);
    $table->string('fiat_currency', 3);
    $table->decimal('exchange_rate', 18, 8);
    $table->string('tx_hash')->nullable();
    $table->boolean('is_shielded')->default(false);
    $table->enum('status', ['pending', 'paid', 'confirmed', 'settled', 'refunded']);
    $table->timestamps();
});

// 2026_02_XX_000003_create_trustcert_tables.php
Schema::create('certificates', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('user_id');
    $table->string('token_id')->unique();
    $table->string('wallet_address');
    $table->enum('cert_type', [
        'PERSONAL_TRUST',
        'BUSINESS_TRUST',
        'DUAL_USE_EXPORT',
        'ACCREDITED_INVESTOR',
        'WHITE_HAT'
    ]);
    $table->string('credential_hash');
    $table->text('encrypted_data');
    $table->enum('status', ['pending', 'active', 'suspended', 'revoked', 'expired']);
    $table->string('blockchain', 50);
    $table->string('contract_address');
    $table->string('mint_tx_hash')->nullable();
    $table->timestamp('issued_at')->nullable();
    $table->timestamp('expires_at');
    $table->timestamp('revoked_at')->nullable();
    $table->string('revocation_reason')->nullable();
    $table->timestamps();
});

Schema::create('certificate_applications', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('user_id');
    $table->enum('cert_type', [...]);
    $table->enum('applicant_type', ['INDIVIDUAL', 'BUSINESS']);
    $table->json('applicant_data');
    $table->enum('status', [
        'draft', 'submitted', 'under_review',
        'additional_info_required', 'approved',
        'rejected', 'issued'
    ]);
    $table->integer('current_step');
    $table->decimal('fee_amount', 18, 2);
    $table->boolean('fee_paid')->default(false);
    $table->timestamp('submitted_at')->nullable();
    $table->timestamp('reviewed_at')->nullable();
    $table->uuid('reviewer_id')->nullable();
    $table->text('rejection_reason')->nullable();
    $table->timestamps();
});
```

### 6.3 Smart Contract Deployments

| Contract | Network | Purpose |
|----------|---------|---------|
| TrustCertSBT | Polygon | Soulbound Token for certificates |
| ShieldPool | Polygon | Privacy pool (RAILGUN fork) |
| PaymentRouter | Polygon | Stablecoin payment routing |
| ProofVerifier | Polygon | ZK proof verification |

### 6.4 External Integrations

| Integration | Purpose | Priority |
|-------------|---------|----------|
| RAILGUN SDK | Privacy pool integration | High |
| Polygon ID | zkKYC verification | High |
| Chainlink CCIP | Cross-chain messaging | Medium |
| The Graph | Blockchain indexing | Medium |
| Arweave | Decentralized credential storage | Medium |

---

## 7. Implementation Roadmap

### Phase 1: Foundation (Weeks 1-4)

| Task | Description | Owner |
|------|-------------|-------|
| Key Sharding | Implement Shamir's Secret Sharing | Backend |
| HSM Integration | Connect to cloud HSM for auth shards | Backend |
| Mobile Scaffold | Expo project with core navigation | Mobile |
| Auth Flow | Passkey + Biometric implementation | Mobile |

### Phase 2: Commerce (Weeks 5-8)

| Task | Description | Owner |
|------|-------------|-------|
| Merchant Onboarding | Registration and verification flow | Backend |
| Payment Protocol | QR generation and payment execution | Backend |
| Merchant SDK | TypeScript SDK for integration | Backend |
| Pay Screen | Scanner and payment confirmation | Mobile |
| Settlement Engine | Batch settlement processing | Backend |

### Phase 3: Privacy Layer (Weeks 9-14)

| Task | Description | Owner |
|------|-------------|-------|
| Shield Pool Contract | Deploy and test on testnet | Blockchain |
| ZK Prover Integration | snarkjs WASM in mobile app | Mobile |
| Audit Vault | Encrypted logging with key sharding | Backend |
| Privacy UI | Shield/unshield flows in app | Mobile |
| Proof of Innocence | Sanctions proof generation | Backend |

### Phase 4: TrustCert (Weeks 15-20)

| Task | Description | Owner |
|------|-------------|-------|
| SBT Contract | TrustCertSBT deployment | Blockchain |
| Application Flow | Multi-step application process | Backend + Mobile |
| Verification Pipeline | Document + background checks | Backend |
| ZK Verification | Proof generation for certificates | Backend |
| Certificate UI | Management and verification screens | Mobile |

### Phase 5: Polish & Launch (Weeks 21-24)

| Task | Description | Owner |
|------|-------------|-------|
| Security Audit | Third-party audit (Trail of Bits) | Security |
| Beta Testing | TestFlight + Play Console beta | QA |
| Documentation | API docs, user guides | Docs |
| App Store Prep | Screenshots, descriptions, review | Marketing |
| Mainnet Launch | Production deployment | DevOps |

---

## 8. Security Considerations

### 8.1 Threat Model

| Threat | Mitigation |
|--------|------------|
| Key Compromise | Shamir sharding (2-of-3), no single point of failure |
| Replay Attacks | Nonce-based signing, session binding |
| Privacy Leakage | ZK proofs, encrypted audit logs |
| Regulatory Seizure | Multi-party decryption (3-of-5 key holders) |
| Smart Contract Exploit | Formal verification, timelocks, upgradability |

### 8.2 Compliance Requirements

| Regulation | Requirement | Implementation |
|------------|-------------|----------------|
| GDPR | Data minimization | zkKYC, encrypted storage |
| MiCA | Transaction monitoring | Audit vault, pattern detection |
| Travel Rule | Beneficiary identification | Selective disclosure proofs |
| AML/CFT | Sanctions screening | Proof of Innocence |

---

## 9. Success Metrics

| Metric | Target (6 months) |
|--------|-------------------|
| Mobile App Downloads | 50,000 |
| Monthly Active Users | 20,000 |
| Transaction Volume | $10M |
| TrustCerts Issued | 500 |
| Merchant Partners | 100 |
| Privacy Pool TVL | $5M |

---

## 10. Open Questions

1. **Privacy Pool Jurisdiction**: Which legal entity operates the shield pool?
2. **TrustCert Pricing**: Fee structure for different certificate types?
3. **Merchant Fees**: Revenue split between FinAegis and merchants?
4. **Multi-Chain Strategy**: Deploy on which L2s first (Polygon, Base, Arbitrum)?
5. **Hardware Wallet Integration**: Support Ledger/Trezor for privacy transactions?

---

## Appendix A: Glossary

| Term | Definition |
|------|------------|
| **Shamir's Secret Sharing** | Cryptographic algorithm to split secrets into shards |
| **ZK-SNARK** | Zero-Knowledge Succinct Non-Interactive Argument of Knowledge |
| **Soulbound Token (SBT)** | Non-transferable NFT for credentials |
| **Proof of Innocence** | Cryptographic proof that funds are not from sanctioned sources |
| **Shield Pool** | Privacy pool where funds are mixed using ZK proofs |
| **TEE** | Trusted Execution Environment (secure hardware enclave) |
| **HSM** | Hardware Security Module for key storage |

---

*Document Version: 1.0*
*Last Updated: February 2026*
*Author: FinAegis Architecture Team*

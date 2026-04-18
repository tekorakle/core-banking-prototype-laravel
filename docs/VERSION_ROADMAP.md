# FinAegis Version Roadmap

## Strategic Vision

FinAegis is a **production-grade open-source core banking platform** with world-class developer experience, comprehensive test coverage, and production-ready deployment capabilities.

---

## Version 1.1.0 - Foundation Hardening (COMPLETED)

**Release Date**: January 11, 2026
**Theme**: Code Quality & Test Coverage

### Achievements

| Metric | Before | After | Improvement |
|--------|--------|-------|-------------|
| PHPStan Level | 5 | **8** | +3 levels |
| PHPStan Baseline | 54,632 lines | 9,007 lines | **83% reduction** |
| Test Files | 458 | 499 | +41 files |
| Behat Features | 1 | 22 | +21 features |
| Domain Test Suites | Partial | Complete | 6 new suites |

### Delivered Features
- Comprehensive domain unit tests (Banking, Governance, User, Compliance, Treasury, Lending)
- PHPStan Level 8 compliance with null-safe operators
- CI/CD security audit enforcement
- Event sourcing aggregate return type fixes

---

## Version 1.2.0 - Feature Completion (COMPLETED)

**Release Date**: January 13, 2026
**Theme**: Complete the Platform, Bridge the Gaps

### Achievements

| Category | Deliverables |
|----------|--------------|
| Integration Bridges | Agent-Payment, Agent-KYC, Agent-MCP bridges |
| Enhanced Features | Yield Optimization, EDD Workflows, Batch Processing |
| Observability | 10 Grafana dashboards, Prometheus alerting rules |
| Domain Completions | StablecoinReserve model, Paysera integration |
| TODO Cleanup | 10 TODOs resolved, 2 deferred (external blockers) |

### Focus Areas

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    v1.2.0 FEATURE COMPLETION                            │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐    │
│  │   INTEGRATION   │    │    ENHANCED     │    │   PRODUCTION    │    │
│  │     BRIDGES     │    │    FEATURES     │    │    READINESS    │    │
│  │                 │    │                 │    │                 │    │
│  │ • Agent-Payment │    │ • Yield Optim.  │    │ • Metrics       │    │
│  │ • Agent-KYC     │    │ • EDD Workflows │    │ • Dashboards    │    │
│  │ • Agent-AI      │    │ • Batch Process │    │ • Alerting      │    │
│  └─────────────────┘    └─────────────────┘    └─────────────────┘    │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### Priority 1: Integration Bridges (Phase 6 Completion)

#### 1.1 Agent Payment Bridge
```php
// Connect Agent Protocol to Payment System
class AgentPaymentBridgeService
{
    public function linkWalletToAccount(string $agentDid, string $accountId): void;
    public function processAgentPayment(AgentTransaction $tx): PaymentResult;
    public function syncBalances(string $agentDid): void;
}
```
**Impact**: Enables AI agents to execute real financial transactions
**Effort**: Medium | **Value**: Critical

#### 1.2 Agent Compliance Bridge
```php
// Unified KYC across human and AI agents
class AgentComplianceBridgeService
{
    public function inheritKycFromUser(string $agentDid, string $userId): void;
    public function mapAgentKycTier(AgentKycLevel $level): ComplianceTier;
    public function verifyAgentCompliance(string $agentDid): ComplianceResult;
}
```
**Impact**: Regulatory compliance for AI-driven transactions
**Effort**: Medium | **Value**: Critical

#### 1.3 Agent MCP Bridge
```php
// AI Framework integration with Agent Protocol
class AgentMCPBridgeService
{
    public function executeToolAsAgent(string $agentDid, MCPTool $tool): ToolResult;
    public function registerAgentTools(Agent $agent): void;
    public function auditAgentToolUsage(string $agentDid): AuditLog;
}
```
**Impact**: AI agents can use banking tools with proper authorization
**Effort**: Medium | **Value**: High

### Priority 2: Enhanced Features

#### 2.1 Treasury Yield Optimization
```php
// Complete the portfolio optimization system
class YieldOptimizationService
{
    public function optimizePortfolio(Portfolio $portfolio): OptimizationResult;
    public function calculateExpectedYield(Portfolio $portfolio): YieldProjection;
    public function suggestRebalancing(Portfolio $portfolio): RebalancingPlan;
    public function backtest(Strategy $strategy, DateRange $period): BacktestResult;
}
```
**Impact**: Automated treasury management
**Effort**: High | **Value**: High

#### 2.2 Enhanced Due Diligence (EDD)
```php
// Advanced compliance workflows
class EnhancedDueDiligenceService
{
    public function initiateEDD(string $customerId): EDDWorkflow;
    public function collectDocuments(EDDWorkflow $workflow, array $documents): void;
    public function performRiskAssessment(EDDWorkflow $workflow): RiskScore;
    public function schedulePeriodicReview(string $customerId, Interval $interval): void;
}
```
**Impact**: Regulatory compliance for high-risk customers
**Effort**: Medium | **Value**: High

#### 2.3 Batch Processing Completion
```php
// Complete scheduled and cancellation logic
class BatchProcessingService
{
    public function scheduleBatch(Batch $batch, Carbon $executeAt): string;
    public function cancelScheduledBatch(string $batchId): bool;
    public function processBatchWithProgress(Batch $batch): BatchResult;
    public function retryFailedItems(string $batchId): BatchResult;
}
```
**Impact**: Efficient bulk operations
**Effort**: Low | **Value**: Medium

### Priority 3: Production Readiness

#### 3.1 Observability Stack
```yaml
Metrics:
  - API response times (p50, p95, p99)
  - Transaction processing latency
  - Queue depths and processing times
  - Event sourcing replay times
  - NAV calculation accuracy

Dashboards:
  - Platform Health Overview
  - Domain-specific dashboards (Exchange, Lending, Treasury)
  - Agent Protocol activity
  - Compliance monitoring
  - Financial reconciliation
```

#### 3.2 Alerting Rules
```yaml
Critical Alerts:
  - Transaction settlement failures
  - Compliance check timeouts
  - NAV calculation deviations > 0.1%
  - Database replication lag > 5s
  - Queue backlog > 10,000 items

Warning Alerts:
  - API error rate > 1%
  - Response time p99 > 2s
  - Cache hit rate < 80%
  - Disk usage > 80%
```

### Success Metrics v1.2.0

| Metric | Current | Target |
|--------|---------|--------|
| TODO/FIXME Items | 14 | 0 |
| Phase 6 Integration | Incomplete | Complete |
| Grafana Dashboards | 0 | 10+ |
| Alert Rules | Basic | Comprehensive |
| Agent Protocol Coverage | 60% | 95% |

---

## Version 1.4.1 - Cache Configuration Fix (COMPLETED)

**Release Date**: January 27, 2026
**Theme**: Production Stability Patch

### Summary

Fixes a critical issue where `php artisan optimize` fails in production with "Access denied for user 'root'@'localhost'" error during the `laravel-data` caching step.

### Root Cause

When `DB_CACHE_CONNECTION` was not set in the environment file, Laravel's database cache driver would not properly inherit the configured database credentials, instead falling back to hardcoded MySQL defaults (`root` with empty password).

### Fix Applied

| File | Change |
|------|--------|
| `config/cache.php` | `DB_CACHE_CONNECTION` now defaults to `DB_CONNECTION` value |
| `config/cache.php` | `lock_connection` also inherits from `DB_CONNECTION` |
| `.env.example` | Added documentation for `DB_CACHE_CONNECTION` option |

### Upgrade Notes

No action required. The fix automatically uses your configured `DB_CONNECTION` for cache operations when `DB_CACHE_CONNECTION` is not explicitly set.

---

## Version 1.4.0 - Test Coverage Expansion (COMPLETED)

**Release Date**: January 27, 2026
**Theme**: Comprehensive Domain Test Coverage

### Achievements

| Category | Deliverables |
|----------|--------------|
| AI Domain | 55 unit tests (ConsensusBuilder, AIAgentService, ToolRegistry) |
| Batch Domain | 37 unit tests (ProcessBatchItemActivity, BatchJobData) |
| CGO Domain | 70 unit tests (CgoKycService, InvestmentAgreementService, etc.) |
| FinancialInstitution Domain | 65 unit tests (ComplianceCheckService, PaymentVerificationService, etc.) |
| Fraud Domain | 18 unit tests for FraudDetectionService |
| Wallet Domain | 37 unit tests (KeyManagementService + Value Objects) |
| Regulatory Domain | 13 unit tests for ReportGeneratorService |
| Stablecoin Domain | 24 unit tests for Value Objects |
| Test Utilities | InvokesPrivateMethods helper trait |
| **Total** | **319 new domain tests** |

### Security Hardening

| Fix | Impact |
|-----|--------|
| Rate limiting threshold | Reduced auth attempts from 5 to 3 (brute force protection) |
| Session limit | Reduced max concurrent sessions from 5 to 3 |
| Token expiration | All auth controllers now use `createTokenWithScopes()` |
| API scope bypass | Removed backward compatibility bypass in `CheckApiScope` |
| Agent scope bypass | `AgentScope::hasScope()` returns false for empty scopes |

### CI/CD Improvements

- Deploy workflow improvements with proper skip handling
- Redis service for pre-deployment tests
- Fixed tar file changed warning
- APP_KEY environment variable for build artifacts

---

## Version 1.3.0 - Platform Modularity ✅ COMPLETED

**Release Date**: January 25, 2026
**GitHub Release**: https://github.com/FinAegis/core-banking-prototype-laravel/releases/tag/v1.3.0
**Theme**: Pick-and-Choose Domain Installation

### Architecture Vision

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    v1.3.0 MODULAR ARCHITECTURE                          │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌───────────────────────────────────────────────────────────────────┐ │
│  │                         CORE PLATFORM                              │ │
│  │  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐             │ │
│  │  │ Account │  │Compliance│  │  CQRS   │  │  Event  │             │ │
│  │  │ Domain  │  │  Domain  │  │   Bus   │  │Sourcing │             │ │
│  │  └─────────┘  └─────────┘  └─────────┘  └─────────┘             │ │
│  └───────────────────────────────────────────────────────────────────┘ │
│                              ▲ Required                                 │
│  ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─│─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ │
│                              ▼ Optional                                 │
│  ┌───────────────────────────────────────────────────────────────────┐ │
│  │                        OPTIONAL MODULES                            │ │
│  │  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐             │ │
│  │  │Exchange │  │ Lending │  │Treasury │  │Stablecn │             │ │
│  │  └─────────┘  └─────────┘  └─────────┘  └─────────┘             │ │
│  │  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐             │ │
│  │  │Governnce│  │  Agent  │  │   AI    │  │  Wallet │             │ │
│  │  │         │  │Protocol │  │Framework│  │         │             │ │
│  │  └─────────┘  └─────────┘  └─────────┘  └─────────┘             │ │
│  └───────────────────────────────────────────────────────────────────┘ │
│                                                                         │
│  ┌───────────────────────────────────────────────────────────────────┐ │
│  │                     REFERENCE IMPLEMENTATIONS                      │ │
│  │  ┌─────────────────────────────────────────────────────────────┐  │ │
│  │  │                         GCU BASKET                           │  │ │
│  │  │      (Global Currency Unit - Complete Example)               │  │ │
│  │  └─────────────────────────────────────────────────────────────┘  │ │
│  └───────────────────────────────────────────────────────────────────┘ │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### Domain Decoupling Strategy

#### 3.1 Interface Extraction
```php
// Shared contracts for cross-domain communication
namespace App\Domain\Shared\Contracts;

interface AccountOperationsInterface
{
    public function debit(AccountId $id, Money $amount, string $reference): void;
    public function credit(AccountId $id, Money $amount, string $reference): void;
    public function getBalance(AccountId $id, ?Currency $currency = null): Money;
    public function freeze(AccountId $id, string $reason): void;
}

interface ComplianceGatewayInterface
{
    public function checkKycStatus(string $entityId): KycStatus;
    public function performAmlScreening(Transaction $tx): ScreeningResult;
    public function validateTransactionLimits(Transaction $tx): ValidationResult;
}

interface ExchangeRateProviderInterface
{
    public function getRate(Currency $from, Currency $to): ExchangeRate;
    public function convert(Money $amount, Currency $targetCurrency): Money;
}
```

#### 3.2 Module Manifest System
```json
// app/Domain/Exchange/module.json
{
    "name": "finaegis/exchange",
    "version": "1.0.0",
    "description": "Trading and order matching engine",
    "dependencies": {
        "finaegis/account": "^1.0",
        "finaegis/compliance": "^1.0"
    },
    "optional": {
        "finaegis/wallet": "^1.0"
    },
    "provides": {
        "services": [
            "OrderMatchingServiceInterface",
            "LiquidityPoolServiceInterface"
        ],
        "events": [
            "OrderPlaced", "OrderMatched", "TradeExecuted"
        ]
    },
    "routes": "Routes/api.php",
    "migrations": "Database/Migrations",
    "config": "Config/exchange.php"
}
```

#### 3.3 Domain Installation Commands
```bash
# Install specific domains
php artisan domain:install exchange
php artisan domain:install lending
php artisan domain:install governance

# List available domains
php artisan domain:list

# Check domain dependencies
php artisan domain:dependencies exchange

# Remove unused domain
php artisan domain:remove lending --force
```

### GCU Reference Separation

#### 3.4 Example Directory Structure
```
examples/
└── gcu-basket/
    ├── README.md                 # Installation guide
    ├── composer.json             # Package dependencies
    ├── src/
    │   ├── GCUServiceProvider.php
    │   ├── Config/
    │   │   └── gcu.php          # Basket composition config
    │   ├── Services/
    │   │   ├── GCUBasketService.php
    │   │   ├── NAVCalculationService.php
    │   │   └── RebalancingService.php
    │   ├── Aggregates/
    │   ├── Events/
    │   └── Workflows/
    ├── database/
    ├── routes/
    └── tests/
```

### Success Metrics v1.3.0

| Metric | Current | Target |
|--------|---------|--------|
| Cross-domain Dependencies | Tight | Loose (Interface-based) |
| Module Installation Time | N/A | < 5 minutes |
| Domain Removal | Breaking | Non-breaking |
| GCU Separation | Integrated | Standalone Package |
| Developer Onboarding | 2+ hours | < 30 minutes |

---

## Version 2.0.0 - Multi-Tenancy ✅ COMPLETED

**Release Date**: January 28, 2026
**GitHub Release**: https://github.com/FinAegis/core-banking-prototype-laravel/releases/tag/v2.0.0
**Theme**: Enterprise-Ready Multi-Tenant Platform

### Delivered Features

| Phase | Deliverable | PR |
|-------|-------------|----|
| Phase 1 | Foundation POC - stancl/tenancy v3.9 setup | #328 |
| Phase 2 | Migration Infrastructure - 14 tenant migrations | #329, #337 |
| Phase 3 | Event Sourcing Integration | #330 |
| Phase 4 | Model Scoping - 83 models | #331 |
| Phase 5 | Queue Job Tenant Context | #332 |
| Phase 6 | WebSocket Channel Authorization | #333 |
| Phase 7 | Filament Admin Tenant Filtering | #334 |
| Phase 8 | Data Migration Tooling | #335 |
| Phase 9 | Security Audit | #336 |

---

## Version 2.1.0 - Security & Enterprise Features ✅ COMPLETED

**Release Date**: January 30, 2026
**GitHub Release**: https://github.com/FinAegis/core-banking-prototype-laravel/releases/tag/v2.1.0
**Theme**: Security Hardening & Enterprise Features

### Delivered Features

| Feature | Status | PR |
|---------|--------|-----|
| Hardware Wallet Integration (Ledger, Trezor) | ✅ Complete | #341 |
| Multi-Signature Wallet Support (M-of-N) | ✅ Complete | #342 |
| Real-time WebSocket Streaming | ✅ Complete | #343 |
| Kubernetes Native (Helm Charts, HPA, Istio) | ✅ Complete | #344 |
| Security Hardening (ECDSA, PBKDF2, EIP-2) | ✅ Complete | #345 |

### Strategic Pillars

```
┌─────────────────────────────────────────────────────────────────────────┐
│                       v2.0.0 MAJOR EVOLUTION                            │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │                     MULTI-TENANCY                                │   │
│  │  • Tenant isolation at database level                           │   │
│  │  • Per-tenant configuration and branding                        │   │
│  │  • Cross-tenant compliance boundaries                           │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                         │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │                     BLOCKCHAIN NATIVE                            │   │
│  │  • Multi-signature wallet support                               │   │
│  │  • Hardware wallet integration (Ledger, Trezor)                 │   │
│  │  • Cross-chain bridges (EVM, Solana, Cosmos)                    │   │
│  │  • Smart contract deployment and management                     │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                         │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │                     REAL-TIME INFRASTRUCTURE                     │   │
│  │  • WebSocket event streaming                                    │   │
│  │  • Real-time order book updates                                 │   │
│  │  • Live NAV calculations                                        │   │
│  │  • Push notifications for transactions                          │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                         │
│  ┌─────────────────────────────────────────────────────────────────┐   │
│  │                     KUBERNETES NATIVE                            │   │
│  │  • Helm charts for all components                               │   │
│  │  • Horizontal Pod Autoscaling                                   │   │
│  │  • Service mesh integration (Istio)                             │   │
│  │  • GitOps deployment workflows                                  │   │
│  └─────────────────────────────────────────────────────────────────┘   │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### Feature Set

#### Multi-Tenancy Architecture
```php
// Tenant-aware infrastructure
class TenantManager
{
    public function setCurrentTenant(Tenant $tenant): void;
    public function getCurrentTenant(): ?Tenant;
    public function runForTenant(Tenant $tenant, callable $callback): mixed;
}

// Database scoping
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('tenant_id', TenantManager::getCurrentTenant()->id);
    }
}
```

#### Hardware Wallet Integration
```php
interface HardwareWalletInterface
{
    public function connect(DeviceType $device): HardwareWallet;
    public function getAccounts(HardwareWallet $wallet): array;
    public function signTransaction(HardwareWallet $wallet, Transaction $tx): SignedTransaction;
    public function verifyAddress(HardwareWallet $wallet, string $path): Address;
}

// Supported devices
enum DeviceType: string
{
    case LEDGER_NANO_S = 'ledger_nano_s';
    case LEDGER_NANO_X = 'ledger_nano_x';
    case TREZOR_ONE = 'trezor_one';
    case TREZOR_MODEL_T = 'trezor_model_t';
}
```

#### Multi-Signature Support
```php
class MultiSigWallet
{
    public function __construct(
        private array $signers,
        private int $requiredSignatures,
    ) {}

    public function initiateTransaction(Transaction $tx, Signer $initiator): PendingTx;
    public function addSignature(PendingTx $tx, Signer $signer, Signature $sig): void;
    public function canExecute(PendingTx $tx): bool;
    public function execute(PendingTx $tx): TransactionResult;
}
```

#### Real-Time Event Streaming
```php
// WebSocket channels
class OrderBookChannel implements PresenceChannel
{
    public function subscribe(string $tradingPair): void;

    public function onOrderPlaced(OrderPlaced $event): void
    {
        $this->broadcast('order.placed', $event->toArray());
    }

    public function onTradeExecuted(TradeExecuted $event): void
    {
        $this->broadcast('trade.executed', $event->toArray());
    }
}

// Client SDK
const orderBook = new FinAegisWebSocket();
orderBook.subscribe('BTC/USD', {
    onOrder: (order) => updateOrderBook(order),
    onTrade: (trade) => updateTrades(trade),
    onNAV: (nav) => updateNAV(nav),
});
```

### Success Metrics v2.0.0

| Metric | Target |
|--------|--------|
| Multi-tenant Support | Full isolation |
| Hardware Wallet Coverage | Ledger + Trezor |
| Real-time Latency | < 50ms |
| Kubernetes Deployment | One-click |
| Cross-chain Support | 5+ networks |

---

## Version 2.2.0 - Mobile Backend Infrastructure ✅ COMPLETED

**Release Date**: January 31, 2026
**GitHub Release**: https://github.com/FinAegis/core-banking-prototype-laravel/releases/tag/v2.2.0
**Theme**: Mobile-First Backend Services
**Next Step**: `finaegis-mobile` React Native app (separate repository)

### Delivered Features

| Feature | Status | PRs |
|---------|--------|-----|
| Mobile Device Management | ✅ Complete | #347 |
| ECDSA P-256 Biometric Auth | ✅ Complete | #347 |
| Push Notification Service | ✅ Complete | #347 |
| Session Management | ✅ Complete | #347 |
| Tenant-Aware Jobs | ✅ Complete | #350 |
| API Endpoints | ✅ Complete | #351 |
| Event Listeners | ✅ Complete | #352 |
| Comprehensive Tests | ✅ Complete | #355 |
| API Standardization | ✅ Complete | #356 |
| CI/CD Optimization | ✅ Complete | #357-359 |
| WebSocket Broadcasting | ✅ Complete | #360 |

### Overview

Complete backend infrastructure for Android/iOS mobile wallet application using **Expo (EAS)** that connects to the FinAegis Core Banking API. The mobile app frontend will provide standard wallet functionality including balance management, top-ups, transfers, and real-time notifications.

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    v2.2.0 MOBILE WALLET ARCHITECTURE                     │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌───────────────────────────────────────────────────────────────────┐ │
│  │                     MOBILE APP (Expo/React Native)                 │ │
│  │  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐             │ │
│  │  │ Wallet  │  │ Top-Up  │  │Transfer │  │ Trading │             │ │
│  │  │  Home   │  │ Screen  │  │ Screen  │  │ Screen  │             │ │
│  │  └─────────┘  └─────────┘  └─────────┘  └─────────┘             │ │
│  │  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐             │ │
│  │  │  Cards  │  │ History │  │  KYC    │  │Settings │             │ │
│  │  │  Mgmt   │  │  View   │  │ Upload  │  │ Profile │             │ │
│  │  └─────────┘  └─────────┘  └─────────┘  └─────────┘             │ │
│  └───────────────────────────────────────────────────────────────────┘ │
│                              │                                          │
│  ┌───────────────────────────▼───────────────────────────────────────┐ │
│  │                     API LAYER (TypeScript SDK)                     │ │
│  │  • REST Client   • WebSocket Client   • Push Handler              │ │
│  └───────────────────────────────────────────────────────────────────┘ │
│                              │                                          │
│  ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─│─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ │
│                              ▼                                          │
│  ┌───────────────────────────────────────────────────────────────────┐ │
│  │                 BACKEND ENHANCEMENTS (Core Banking)                │ │
│  │  • Mobile Auth (Biometric)  • Push Notifications (FCM/APNS)       │ │
│  │  • Device Management        • WebSocket Broadcasting               │ │
│  └───────────────────────────────────────────────────────────────────┘ │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### Phase 1: Backend API Enhancements (2-3 weeks)

#### 1.1 Mobile Device Management
```php
// New endpoints for device registration
POST   /api/mobile/devices                    # Register device
DELETE /api/mobile/devices/{device_id}        # Unregister device
GET    /api/mobile/devices                    # List user devices

// Device model tracks:
- device_id (UUID)
- user_id (FK)
- platform (ios/android)
- push_token (FCM/APNS token)
- device_name
- app_version
- last_active_at
- biometric_enabled (boolean)
```

#### 1.2 Push Notification Infrastructure
```php
// Firebase Cloud Messaging (FCM) for Android
// Apple Push Notification Service (APNS) for iOS

// Notification types:
- transaction.received    # Incoming payment
- transaction.sent        # Outgoing payment confirmed
- transaction.failed      # Transaction failure
- balance.low             # Low balance alert
- kyc.status_changed      # KYC verification update
- security.login          # New device login
- price.alert             # Price movement alert (optional)
```

#### 1.3 Biometric Authentication
```php
// Device-bound authentication
POST   /api/mobile/auth/biometric/enable     # Enable biometric
POST   /api/mobile/auth/biometric/verify     # Verify biometric token
DELETE /api/mobile/auth/biometric/disable    # Disable biometric

// Flow:
1. User logs in with email/password
2. Prompts to enable biometric
3. Stores device-bound key in secure enclave
4. Future logins use biometric + device key
```

#### 1.4 WebSocket Broadcasting Activation
```php
// Enable Soketi for real-time updates
// Wire domain events to broadcasts:

AccountBalanceUpdated    → tenant.{id}.accounts
TransactionCompleted     → tenant.{id}.transactions
OrderPlaced/Matched      → tenant.{id}.exchange
```

### Phase 2: Mobile App Foundation (3-4 weeks)

#### 2.1 Technology Stack

| Layer | Technology |
|-------|------------|
| **Framework** | Expo SDK 52+ (React Native) |
| **Build Service** | EAS Build (Expo Application Services) |
| **State Management** | Zustand + React Query |
| **Navigation** | Expo Router (file-based) |
| **UI Components** | NativeWind (Tailwind for RN) + Expo UI |
| **Secure Storage** | expo-secure-store |
| **Biometrics** | expo-local-authentication |
| **Push Notifications** | expo-notifications + FCM/APNS |
| **WebSocket** | Socket.io or Pusher React Native |
| **Forms** | React Hook Form + Zod |
| **Charts** | Victory Native or react-native-charts-wrapper |

#### 2.2 App Screens

```
finaegis-mobile/
├── app/                          # Expo Router screens
│   ├── (auth)/                   # Auth group (unauthenticated)
│   │   ├── login.tsx
│   │   ├── register.tsx
│   │   ├── forgot-password.tsx
│   │   └── verify-2fa.tsx
│   ├── (tabs)/                   # Main app (authenticated)
│   │   ├── index.tsx             # Home/Dashboard
│   │   ├── wallet.tsx            # Wallet & Balances
│   │   ├── transactions.tsx      # Transaction History
│   │   ├── exchange.tsx          # Trading (optional Phase 2)
│   │   └── settings.tsx          # Settings & Profile
│   ├── topup/
│   │   ├── index.tsx             # Top-up methods
│   │   ├── bank-transfer.tsx     # Bank transfer instructions
│   │   └── card.tsx              # Card top-up (future)
│   ├── transfer/
│   │   ├── index.tsx             # Send money
│   │   ├── recipient.tsx         # Select recipient
│   │   ├── amount.tsx            # Enter amount
│   │   └── confirm.tsx           # Confirm & send
│   ├── receive/
│   │   └── index.tsx             # QR code & account details
│   ├── kyc/
│   │   ├── index.tsx             # KYC status
│   │   └── upload.tsx            # Document upload
│   └── _layout.tsx               # Root layout
├── components/                    # Shared components
│   ├── BalanceCard.tsx
│   ├── TransactionItem.tsx
│   ├── BiometricPrompt.tsx
│   └── ...
├── services/                      # API services
│   ├── api.ts                    # REST client
│   ├── websocket.ts              # WebSocket client
│   └── push.ts                   # Push notification handler
├── stores/                        # Zustand stores
│   ├── auth.ts
│   ├── wallet.ts
│   └── settings.ts
└── utils/                         # Utilities
    ├── formatters.ts
    ├── validators.ts
    └── crypto.ts
```

### Phase 3: Core Features Implementation (4-5 weeks)

#### 3.1 Authentication & Security

| Feature | Description |
|---------|-------------|
| Email/Password Login | Standard login with Sanctum tokens |
| 2FA Support | TOTP verification screen |
| Biometric Login | Face ID / Fingerprint after initial setup |
| Session Management | Automatic token refresh, logout on inactivity |
| Device Binding | Link biometric auth to specific device |

#### 3.2 Wallet & Balances

| Feature | Description |
|---------|-------------|
| Multi-Asset Dashboard | Show all asset balances (fiat, crypto, GCU) |
| Balance Refresh | Pull-to-refresh + real-time WebSocket updates |
| Asset Details | Tap asset for detailed view with mini-chart |
| Portfolio Value | Total value in user's preferred currency |

#### 3.3 Top-Up Methods

| Method | Implementation |
|--------|----------------|
| Bank Transfer | Display IBAN/account details for manual transfer |
| Custodian Banks | Paysera, Deutsche Bank integration |
| Crypto Deposit | Show wallet address with QR code |
| Card Payment | Future: Stripe integration for card top-ups |

#### 3.4 Transfers & Payments

| Feature | Description |
|---------|-------------|
| P2P Transfer | Send to another FinAegis account |
| External Transfer | Bank transfers via custodian |
| QR Code Payments | Scan QR to pay, generate QR to receive |
| Transaction Confirmation | Biometric/PIN confirmation for sends |
| Transfer History | Filterable transaction list |

#### 3.5 Transaction History

| Feature | Description |
|---------|-------------|
| Infinite Scroll | Paginated history with lazy loading |
| Filters | By date, type, asset, status |
| Search | Search by reference, recipient, amount |
| Export | Download CSV/PDF statement |
| Real-time Updates | Push notification + list refresh |

### Phase 4: Advanced Features (3-4 weeks)

#### 4.1 GCU Trading

| Feature | Description |
|---------|-------------|
| Buy GCU | Purchase GCU with fiat/crypto |
| Sell GCU | Redeem GCU to fiat/crypto |
| Price Chart | Historical GCU price visualization |
| Trading Limits | Display user's daily/monthly limits |

#### 4.2 KYC/Compliance

| Feature | Description |
|---------|-------------|
| KYC Status | Show current verification level |
| Document Upload | Camera/gallery for ID documents |
| Selfie Verification | Liveness check integration |
| Status Tracking | Push notification on approval/rejection |

#### 4.3 Notifications

| Feature | Description |
|---------|-------------|
| Push Notifications | FCM (Android) / APNS (iOS) |
| In-App Notifications | Notification center with history |
| Notification Preferences | User can toggle notification types |

### Success Metrics v2.2.0

| Metric | Target |
|--------|--------|
| App Store Rating | 4.5+ stars |
| Crash-Free Sessions | 99.5%+ |
| Cold Start Time | < 2 seconds |
| API Response Time | < 500ms (p95) |
| Push Delivery Rate | > 95% |
| Biometric Adoption | > 70% of users |
| Daily Active Users | Track baseline |

### Backend Changes Required (Core Banking)

| File/Feature | Changes |
|--------------|---------|
| `app/Models/MobileDevice.php` | New model for device tracking |
| `database/migrations/` | Mobile devices table |
| `app/Http/Controllers/Api/MobileController.php` | Device & biometric endpoints |
| `app/Services/PushNotificationService.php` | FCM/APNS integration |
| `config/broadcasting.php` | Soketi configuration |
| `app/Listeners/BroadcastEventListener.php` | Wire events to broadcasts |
| `.env.example` | Add FCM/APNS credentials |

### New Repository Structure

```
finaegis-mobile/
├── .github/
│   └── workflows/
│       ├── build-android.yml      # EAS build for Android
│       ├── build-ios.yml          # EAS build for iOS
│       └── test.yml               # Jest tests
├── app/                           # Expo Router pages
├── assets/                        # Images, fonts
├── components/                    # Reusable UI components
├── services/                      # API clients
├── stores/                        # State management
├── utils/                         # Helpers
├── app.json                       # Expo configuration
├── eas.json                       # EAS Build configuration
├── package.json
├── tsconfig.json
└── README.md
```

---

## Version 2.3.0 - Industry Leadership ✅ COMPLETED

**Released**: February 1, 2026
**Theme**: AI Framework & BaaS Foundation
**GitHub Release**: https://github.com/FinAegis/core-banking-prototype-laravel/releases/tag/v2.3.0

### Delivered Features

#### AI Framework Foundation ✅
- MCP Server implementation with Tool Registry
- LLM Orchestration Service (multi-provider support)
- Natural Language Processor Service
- Prompt Template Service
- Trading and Risk Assessment Workflows
- Human-in-the-Loop Approval Workflows
- AI Interaction Event Sourcing (Aggregate)

#### RegTech Foundation ✅
- Jurisdiction Configuration Service
- Regulatory Calendar Service
- RegTech Orchestration Service
- Filing Schedule and Regulatory Endpoint models

#### BaaS Configuration ✅
- Partner tier system (Starter, Growth, Enterprise)
- White-label branding configuration
- SDK generation settings
- Widget configuration
- Partner billing configuration
- Marketplace integration settings

### Deferred to v2.5.0
- Natural language transaction query API endpoints
- ML anomaly detection activities
- MiFID II / MiCA compliance services
- Regulatory API adapters (FinCEN, ESMA, FCA, MAS)
- SDK generation implementation
- Embeddable widgets implementation
- Partner billing implementation

---

## UX/UI Roadmap

### Current State Assessment

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    CURRENT UI/UX INVENTORY                              │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ADMIN PANEL (Filament 3.0)                                            │
│  ├── Account Management ............... ██████████ Complete            │
│  ├── Compliance Dashboard ............. ████████░░ 80%                 │
│  ├── Exchange Monitoring .............. ██████░░░░ 60%                 │
│  ├── Treasury Operations .............. ████░░░░░░ 40%                 │
│  └── Agent Protocol Admin ............. ██████░░░░ 60%                 │
│                                                                         │
│  PUBLIC WEBSITE                                                         │
│  ├── Landing Pages .................... ██████████ Complete            │
│  ├── Documentation .................... ████████░░ 80%                 │
│  └── API Playground ................... ░░░░░░░░░░ Not Started         │
│                                                                         │
│  API DOCUMENTATION (Swagger)                                            │
│  ├── Account API ...................... ██████████ Complete            │
│  ├── Exchange API ..................... ████████░░ 80%                 │
│  ├── Agent Protocol API ............... ██████░░░░ 60%                 │
│  └── Interactive Examples ............. ██░░░░░░░░ 20%                 │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### UX Improvements by Version

#### v1.2.0 - Operational Excellence
```
Priority UX Enhancements:
• Real-time transaction status indicators
• Compliance workflow progress visualization
• Enhanced error messages with recovery suggestions
• Dashboard widgets for key metrics
• Notification center with action items
```

#### v1.3.0 - Developer Experience
```
Developer-Focused UX:
• Interactive API playground with code generation
• Domain installation wizard
• Visual dependency graph explorer
• Configuration validation UI
• One-click demo environment
```

#### v2.0.0 - Professional Polish
```
Enterprise UX Features:
• Multi-tenant dashboard customization
• White-label theming engine
• Accessibility compliance (WCAG 2.1 AA)
• Mobile-responsive admin panel
• Dark mode across all interfaces
• Keyboard shortcuts for power users
```

---

## Risk Mitigation

### Technical Risks

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Breaking changes in modularity | Medium | High | Comprehensive integration tests |
| Performance regression | Low | High | Benchmark suite, load testing |
| Security vulnerabilities | Low | Critical | Regular security audits, bug bounty |
| Third-party dependency issues | Medium | Medium | Dependency pinning, alternatives |

### Organizational Risks

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Scope creep | High | Medium | Strict version boundaries |
| Resource constraints | Medium | High | Prioritization, community contributions |
| Market timing | Low | Medium | Continuous delivery model |

---

## Governance & Release Process

### Version Numbering

```
MAJOR.MINOR.PATCH

MAJOR: Breaking changes, significant architecture shifts
MINOR: New features, non-breaking enhancements
PATCH: Bug fixes, security updates, documentation
```

### Release Cadence

```
┌─────────────────────────────────────────────────────────────────────────┐
│                      RELEASE SCHEDULE                                   │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  MINOR RELEASES (1.x.0)                                                │
│  └── Every 8-12 weeks                                                  │
│                                                                         │
│  PATCH RELEASES (1.x.y)                                                │
│  └── As needed (security within 24-48 hours)                           │
│                                                                         │
│  MAJOR RELEASES (x.0.0)                                                │
│  └── Every 6-12 months                                                 │
│                                                                         │
│  LTS RELEASES                                                          │
│  └── Major versions receive 2 years of security support               │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

### Branch Strategy

```
main ─────────●─────────●─────────●─────────●─────────→
              │         │         │         │
              ▼         ▼         ▼         ▼
           release/   release/   release/   release/
           v1.2.0     v1.3.0     v2.0.0     v2.1.0
              │         │         │         │
              ▼         ▼         ▼         ▼
            v1.2.0    v1.3.0    v2.0.0    v2.1.0
            (tag)     (tag)     (tag)     (tag)
```

---

## Summary

| Version | Theme | Key Deliverables | Status |
|---------|-------|------------------|--------|
| **v1.1.0** | Foundation Hardening | PHPStan L8, Test Coverage | ✅ Released 2026-01-11 |
| **v1.2.0** | Feature Completion | Agent Bridges, Yield Optimization | ✅ Released 2026-01-13 |
| **v1.3.0** | Platform Modularity | Domain Decoupling, Module System | ✅ Released 2026-01-25 |
| **v1.4.0** | Test Coverage Expansion | 319 Domain Tests, Security Hardening | ✅ Released 2026-01-27 |
| **v1.4.1** | Patch Release | Database Cache Connection Fix | ✅ Released 2026-01-27 |
| **v2.0.0** | Multi-Tenancy | Team-Based Isolation, 9 Phases | ✅ Released 2026-01-28 |
| **v2.1.0** | Security & Enterprise | Hardware Wallets, K8s, Security Hardening | ✅ Released 2026-01-30 |
| **v2.2.0** | Mobile Backend | Device Mgmt, Biometrics, Push Notifications, WebSocket | ✅ Released 2026-01-31 |
| **v2.3.0** | Industry Leadership | AI Framework, RegTech Foundation, BaaS Config | ✅ Released 2026-02-01 |
| **v2.4.0** | Privacy & Identity | Key Management, Privacy, Commerce, TrustCert | ✅ Released 2026-02-01 |
| **v2.5.0** | Mobile App Launch | Mobile Frontend (Expo/React Native), App Store Release | ✅ Released |
| **v2.6.0** | Privacy Layer & ERC-4337 | Merkle Trees, Smart Accounts, Delegated Proofs, Gas Station | ✅ Released 2026-02-02 |
| **v2.7.0** | Mobile Payment API | Payment Intents, Receipts, Passkey Auth, P2P Transfers | ✅ Released 2026-02-08 |
| **v2.8.0** | AI Query & RegTech | AI Transaction Queries, MiFID II, MiCA, Travel Rule | ✅ Released 2026-02-08 |
| **v2.9.0** | BaaS & Production Hardening | ML Anomaly Detection, BaaS Implementation, SDK Generation | ✅ Released 2026-02-10 |
| **v2.9.1** | Production Hardening | On-Chain SBT, snarkjs, AWS KMS, Azure Key Vault, Security Audit | ✅ Released 2026-02-10 |
| **v2.10.0** | Mobile API Compatibility | ~30 mobile-facing API endpoints, response envelope consistency, wallet/TrustCert/commerce/relayer mobile APIs | ✅ Released 2026-02-10 |
| **v3.0.0** | Cross-Chain & DeFi | CrossChain bridges (Wormhole/LayerZero/Axelar), DeFi protocols (Uniswap/Aave/Curve/Lido), cross-chain swaps, multi-chain portfolio | ✅ Released 2026-02-10 |
| **v3.1.0** | Consolidation & UI | Documentation refresh, Swagger coverage, website features, admin UI (15 domains), user UI, developer portal | ✅ Released 2026-02-11 |
| **v3.2.0** | Production Readiness & Plugin Architecture | Module manifests, enable/disable, modular routes, admin API/UI, k6 tests, query middleware, open-source templates | ✅ Released 2026-02-11 |
| **v3.2.1** | Patch: GitLeaks & Dependencies | GitLeaks false positives fix, 14 dependency updates | ✅ Released 2026-02-12 |
| **v3.3.0** | Event Store & Observability | Event replay/rebuild, real-time dashboards, structured logging, deep health checks | ✅ Released 2026-02-12 |
| **v3.4.0** | API Maturity & DX | API versioning, rate limiting per tier, SDK auto-generation, OpenAPI 100% | ✅ Released 2026-02-12 |
| **v3.5.0** | Compliance Certification | SOC 2, PCI DSS, multi-region, GDPR tooling | ✅ Released 2026-02-12 |
| **v4.0.0** | Architecture Evolution | Event Store v2, GraphQL API, Plugin Marketplace | ✅ Released 2026-02-13 |
| **v4.1.0** | GraphQL Expansion | 6 new GraphQL domains (Treasury, Payment, Lending, Stablecoin, CrossChain, DeFi), event replay filters, projector health monitoring | ✅ Released 2026-02-13 |
| **v4.2.0** | Real-time Platform | GraphQL subscriptions (4 new), plugin hook system (17 hooks), example plugins, 8 core domain mutations | ✅ Released 2026-02-13 |
| **v4.3.0** | Developer Experience | 4 new GraphQL domains, dashboard widget plugin, CLI commands, GraphQL security hardening | ✅ Released 2026-02-13 |
| **v5.0.0** | Streaming Architecture | Event streaming (Redis Streams), live dashboard, notification system, API gateway, GraphQL schema expansion (33 domains) | ✅ Released 2026-02-13 |
| **v5.0.1** | Platform Hardening | GraphQL CQRS alignment (21 mutations), OpenAPI 100%, Plugin Marketplace UI, PHP 8.4 CI, 97 test conversions, doc refresh | ✅ Released 2026-02-13 |
| **v5.1.0** | Mobile API Completeness | 21 mobile endpoints, GraphQL 33-domain full coverage, blockchain models, CI hardening, axios CVE fix | ✅ Released 2026-02-16 |
| **v5.1.1** | Mobile App Landing Page | Landing page at `/app` with email signup, flaky Azure HSM test fix | ✅ Released 2026-02-16 |
| **v5.1.2** | Production Landing Page Fix | Standalone pre-compiled CSS for `/app` (CSP-compliant, Vite-independent) | ✅ Released 2026-02-16 |
| **v5.1.3** | Mobile API Compatibility | Auth response standardization, token refresh/logout-all endpoints, rate limiter fix | ✅ Released 2026-02-17 |
| **v5.1.4** | Refresh Token Mechanism | Proper access/refresh token pairs, token rotation, PHPStan fix, OpenAPI docs update | ✅ Released 2026-02-18 |
| **v5.1.5** | Dependency Cleanup & Production Readiness | l5-swagger 9→10 (swagger-php 6), PSR-4 plugin fix, `.env.production.example` for mobile backend, passkey test fix | ✅ Released 2026-02-21 |
| **v5.2.0** | X402 Protocol | HTTP 402 native micropayments (USDC on Base), payment gate middleware, AI agent payments, spending limits | ✅ Released 2026-02-19 |
| **v5.4.0** | Ondato KYC & Card Issuing | Ondato identity verification, Chainalysis sanctions adapter, Marqeta card issuing, Firebase FCM v1 | ✅ Released 2026-02-21 |
| **v5.5.0** | Production Relayer & Card Webhooks | ERC-4337 Pimlico v2 integration, Marqeta webhook auth, platform hardening | ✅ Released 2026-02-21 |
| **v5.6.0** | RAILGUN Privacy Protocol | Node.js bridge to @railgun-community/wallet SDK, shield/unshield/transfer, 4-chain support (ETH/Polygon/Arbitrum/BSC) | ✅ Released 2026-02-28 |
| **v5.7.0** | Mobile Rewards & Security Hardening | Rewards domain (quests, XP/levels, shop, streaks), WebAuthn FIDO2 hardening, recent recipients, route aliases, 44 tests | ✅ Released 2026-02-28 |

---

## Version 2.4.0 - Privacy & Identity ✅ COMPLETED

**Released**: February 1, 2026
**Theme**: Privacy-Preserving Identity & Secure Key Management
**GitHub Release**: https://github.com/FinAegis/core-banking-prototype-laravel/releases/tag/v2.4.0

### Phase 1: Key Management Foundation ✅ COMPLETED

**PR #364 - Shamir's Secret Sharing**

| Component | Files | Status |
|-----------|-------|--------|
| Enums | `ShardType`, `ShardStatus` | ✅ |
| Value Objects | `KeyShard`, `ReconstructedKey` | ✅ |
| Services | `ShamirService`, `EncryptionService`, `KeyReconstructionService`, `ShardDistributionService` | ✅ |
| HSM | `DemoHsmProvider`, `HsmIntegrationService` | ✅ |
| Events | `KeyReconstructed`, `KeyShardsCreated`, `KeyShardsRotated`, `KeyReconstructionFailed` | ✅ |
| Models | `KeyShardRecord`, `KeyReconstructionLog`, `RecoveryBackup` | ✅ |
| Tests | 57 unit tests, 117 assertions | ✅ |

### Phase 2: Privacy Domain ✅ COMPLETE

**Scope**: Zero-Knowledge KYC, Privacy-Preserving Payments

| Component | Description | Status |
|-----------|-------------|--------|
| `ZkKycService` | Zero-knowledge KYC verification without exposing PII | ✅ |
| `SelectiveDisclosureService` | Prove claims without revealing full data | ✅ |
| `DemoZkProver` | Demo implementation of ZK proof generation | ✅ |
| `ProofOfInnocenceService` | RAILGUN-inspired compliance proofs | ✅ |
| Enums | `ProofType`, `PrivacyLevel` | ✅ |
| Value Objects | `ZkProof`, `SelectiveDisclosure` | ✅ |
| Events | `ZkKycVerified`, `ZkKycVerificationFailed`, `ProofOfInnocenceGenerated` | ✅ |
| Config | `config/privacy.php` with ZK, selective disclosure, POI settings | ✅ |
| Tests | 60 unit tests, 166 assertions | ✅ |

### Phase 3: Commerce Domain ✅ COMPLETE

**Scope**: On-Chain Credentials, Merchant Integration

| Component | Description | Status |
|-----------|-------------|--------|
| `SoulboundTokenService` | Non-transferable tokens for identity/credentials | ✅ |
| `MerchantOnboardingService` | KYC-verified merchant registration with state machine | ✅ |
| `PaymentAttestationService` | Cryptographic attestations for payments/transactions | ✅ |
| `CredentialIssuanceService` | W3C Verifiable Credentials issuance | ✅ |
| Enums | `TokenType`, `MerchantStatus`, `AttestationType`, `CredentialType` | ✅ |
| Value Objects | `SoulboundToken`, `PaymentAttestation`, `VerifiableCredential` | ✅ |
| Events | `SoulboundTokenIssued`, `MerchantOnboarded`, `PaymentAttested`, `CredentialIssued` | ✅ |
| Contracts | `TokenIssuerInterface`, `AttestationServiceInterface` | ✅ |
| Config | `config/commerce.php` with SBT, merchant, attestation settings | ✅ |
| Tests | 66 unit tests, 197 assertions | ✅ |

### Phase 4: TrustCert Domain (Complete ✅)

**Scope**: Verifiable Credentials, Certificate Management, Trust Framework

| Component | Description | Status |
|-----------|-------------|--------|
| **Enums** | | |
| `CertificateStatus` | Certificate lifecycle (pending, active, suspended, revoked, expired) | ✅ |
| `TrustLevel` | Trust levels (unknown, basic, verified, high, ultimate) | ✅ |
| `RevocationReason` | RFC 5280 revocation reasons | ✅ |
| `IssuerType` | Issuer types (root_ca, intermediate_ca, trusted_issuer, etc.) | ✅ |
| **Contracts** | | |
| `CertificateAuthorityInterface` | Certificate lifecycle operations | ✅ |
| `RevocationRegistryInterface` | Revocation list management | ✅ |
| `TrustFrameworkInterface` | Trust framework operations | ✅ |
| **Value Objects** | | |
| `Certificate` | Digital certificate representation | ✅ |
| `RevocationEntry` | Revocation registry entry | ✅ |
| `TrustedIssuer` | Issuer in trust framework | ✅ |
| `TrustChain` | Chain of trust validation | ✅ |
| **Services** | | |
| `CertificateAuthorityService` | Internal CA for credential signing | ✅ |
| `VerifiableCredentialService` | W3C VC standard implementation | ✅ |
| `RevocationRegistryService` | Credential revocation tracking (StatusList2021) | ✅ |
| `TrustFrameworkService` | Multi-issuer trust management | ✅ |
| **Events** | | |
| `CertificateIssued` | Certificate issuance event | ✅ |
| `CertificateRevoked` | Certificate revocation event | ✅ |
| `CredentialRevoked` | Credential revocation event | ✅ |
| `IssuerRegistered` | Issuer registration event | ✅ |
| `TrustLevelChanged` | Trust level change event | ✅ |
| **Config** | | |
| `config/trustcert.php` | CA, credentials, revocation, trust framework settings | ✅ |
| **Tests** | | |
| Unit Tests | 111 tests, 334 assertions | ✅ |

---

## Version 2.5.0 - Mobile App Launch ✅ COMPLETED

**Target**: Q1 2026
**Theme**: Consumer-Ready Mobile Experience
**Repository**: `finaegis-mobile` (Expo/React Native)

### Backend Ready ✅

The following backend features are complete and ready for mobile integration:

| Domain | Features | Status |
|--------|----------|--------|
| **Mobile** (v2.2.0) | Device registration, Biometric auth, Push notifications, Sessions | ✅ |
| **KeyManagement** (v2.4.0) | Shamir sharding, Key reconstruction | ✅ |
| **Privacy** (v2.4.0) | ZK-KYC, Selective disclosure, Proof of Innocence | ✅ |
| **Commerce** (v2.4.0) | Soulbound tokens, Merchant onboarding, Attestations | ✅ |
| **TrustCert** (v2.4.0) | Verifiable credentials, Certificate authority | ✅ |

### NEW Backend Domains (v2.5.0)

Based on mobile architecture review, the following new backend domains are required:

#### Phase 1: Card Issuance Domain 🆕

**Purpose**: Enable tap-to-pay at regular shops using stablecoins via virtual cards.

| Component | Description | Status |
|-----------|-------------|--------|
| `CardProvisioningService` | Apple Pay / Google Pay push provisioning | 🚧 |
| `CardLifecycleService` | Card freeze, cancel, replace operations | 🚧 |
| `JitFundingService` | Just-in-Time authorization (< 2s latency) | 🚧 |
| `MarqetaAdapter` | Marqeta card issuer integration | 🚧 |
| `LithicAdapter` | Lithic card issuer integration | 🚧 |
| `StripeIssuingAdapter` | Stripe Issuing integration | 🚧 |
| `AuthorizationWebhook` | Real-time card authorization decisions | 🚧 |
| Database | `virtual_cards`, `card_authorizations`, `card_settlements` | 🚧 |

**API Endpoints**:
```
POST   /api/v1/cards/provision          # Add to Apple/Google Wallet
GET    /api/v1/cards                    # List user cards
POST   /api/v1/cards/{id}/freeze        # Freeze card
DELETE /api/v1/cards/{id}/freeze        # Unfreeze card
POST   /api/webhooks/card-issuer/auth   # JIT funding webhook
```

#### Phase 2: Gas Relayer Domain 🆕

**Purpose**: Enable users to send stablecoins without needing ETH/MATIC for gas.

| Component | Description | Status |
|-----------|-------------|--------|
| `GasStationService` | Meta-transaction relayer | 🚧 |
| `PaymasterService` | ERC-4337 paymaster implementation | 🚧 |
| `BundlerService` | UserOperation bundling and submission | 🚧 |
| `FeeCalculationService` | Convert gas cost to stablecoin fee | 🚧 |
| Database | `sponsored_transactions`, `gas_refunds` | 🚧 |
| Config | `config/relayer.php` | 🚧 |

**API Endpoints**:
```
POST   /api/v1/relayer/sponsor          # Submit meta-transaction
POST   /api/v1/relayer/estimate         # Estimate gas fee in USDC
GET    /api/v1/relayer/networks         # Supported networks
```

#### Phase 3: TrustCert Presentation 🆕

**Purpose**: Enable QR code / Deep Link verification of TrustCert credentials.

| Component | Description | Status |
|-----------|-------------|--------|
| `PresentationController` | Generate verifiable presentations | 🚧 |
| `QrCodeService` | QR code generation for certificates | 🚧 |
| `DeepLinkService` | Deep link handling for verification | 🚧 |

**API Endpoints**:
```
POST   /api/v1/trustcert/{id}/present   # Generate presentation
GET    /api/v1/trustcert/verify/{token} # Verify presentation
```

### Mobile App Development (Separate Repository)

| Phase | Description | Status |
|-------|-------------|--------|
| **Foundation** | Expo project, navigation, auth flow | 🚧 |
| **Wallet** | Balance display, send/receive, QR codes | 🚧 |
| **Card Payments** | Push provisioning, tap-to-pay | 🚧 |
| **Gas Abstraction** | Stablecoin-only transactions | 🚧 |
| **Privacy** | Shield/unshield (native ZK prover) | 🚧 |
| **TrustCert** | Certificate application, verification | 🚧 |
| **Launch** | TestFlight, Play Console, App Store release | 🚧 |

### Mobile Native Modules Required

| Module | Purpose | Technology |
|--------|---------|------------|
| `@finaegis/react-native-zk-prover` | ZK proof generation | Rust via JSI |
| `@finaegis/react-native-wallet-provisioning` | Apple/Google Pay | Native (Swift/Kotlin) |
| `expo-secure-store` | Secure key storage | Native Keychain/Keystore |
| `expo-local-authentication` | Biometric auth | Native |

### Documentation
- [Mobile App Specification](MOBILE_APP_SPECIFICATION.md) - Complete technical spec (v1.2)
- [Backend Upgrade Plan](BACKEND_UPGRADE_PLAN_v2.4.md) - API integration guide

---

## Version 2.6.0 - Privacy Layer & ERC-4337 Relayer ✅ COMPLETED

**Release Date**: February 2, 2026
**Theme**: Mobile Backend Privacy & Account Abstraction

### Achievements

| Category | Deliverables |
|----------|--------------|
| Privacy Domain | Merkle Trees, Delegated Proofs, SRS Manifest |
| Relayer Domain | Smart Accounts, Gas Station, UserOp Signing |
| Security | Biometric JWT, HSM ECDSA, Balance Checking |
| Quality | Security audit hardening, comprehensive tests |

### Delivered Features
- **MerkleTreeService** - Real-time privacy pool state synchronization
- **DelegatedProofService** - Server-side ZK proof generation for mobile
- **SrsManifestService** - ZK circuit SRS file management
- **SmartAccountService** - ERC-4337 smart account deployment
- **GasStationService** - Enhanced with initCode support
- **UserOperationSigningService** - Auth shard signing with biometric verification
- **BiometricJWTService** - JWT token verification for UserOp signing
- **WalletBalanceService** - Production-ready balance checking

---

## Version 2.7.0 - Mobile Payment API & Enhanced Authentication ✅ COMPLETED

**Release Date**: February 8, 2026
**Theme**: Complete Mobile Payment Infrastructure

### Achievements

| Category | Deliverables |
|----------|--------------|
| MobilePayment Domain | Payment Intents, Receipts, Activity Feed, Network Status |
| Authentication | WebAuthn/Passkey challenge-response endpoints |
| Wallet | P2P transfer helpers (address validation, name resolution, fee quotes) |
| TrustCert | Certificate details and PDF export for mobile |
| Security | Response shape alignment, race condition fixes, idempotency |
| Tests | 17 WalletTransfer + 10 Passkey + 78 MobilePayment tests |

### Delivered Features

#### MobilePayment Domain (NEW - 10 PRs)
- **PaymentIntentService** - Full payment lifecycle with state machine
- **ReceiptService** - Shareable receipts with Redis caching and share URLs
- **ActivityFeedService** - Cursor-paginated feed with type filters
- **ReceiveAddressService** - Deposit address generation per network/asset
- **NetworkAvailabilityService** - Real-time network status
- **FeeEstimationService** - Gas cost estimation with shield surcharges
- **CertificateExportService** - Mobile-spec certificate details and PDF export

#### Authentication
- **PasskeyAuthenticationService** - WebAuthn/FIDO2 with ECDSA P-256 verification
- Passkey registration and credential management on MobileDevice model

#### Wallet P2P Transfer
- **WalletTransferService** - Address validation, ENS/SNS resolution, fee quoting
- Base58 address validation for Solana and Tron networks

### API Endpoints (v2.7.0)

| Category | Endpoints |
|----------|-----------|
| Payment Intents | `POST /v1/payments/intents`, `GET /{id}`, `POST /{id}/submit`, `POST /{id}/cancel` |
| Activity Feed | `GET /v1/activity` |
| Transactions | `GET /v1/transactions/{txId}`, `POST /{txId}/receipt` |
| Wallet | `GET /v1/wallet/receive`, `GET /v1/wallet/validate-address`, `POST /v1/wallet/resolve-name`, `POST /v1/wallet/quote` |
| Network | `GET /v1/networks/status` |
| Passkey | `POST /v1/auth/passkey/challenge`, `POST /v1/auth/passkey/authenticate` |
| TrustCert | `GET /v1/trustcert/{certId}/certificate`, `POST /{certId}/export-pdf` |

---

## Version 2.8.0 - AI Query & Regulatory Technology ✅ COMPLETED

**Release Date**: February 8, 2026
**Theme**: AI-Powered Queries + Multi-Jurisdiction RegTech

### Delivered Features

| Feature | Status | PRs |
|---------|--------|-----|
| AI Transaction Query Tools | ✅ Complete | #397 |
| AI Query API Endpoints | ✅ Complete | #398 |
| RegTech Jurisdiction Adapters (FinCEN, ESMA, FCA, MAS) | ✅ Complete | #399 |
| MiFID II, MiCA, Travel Rule Services + API | ✅ Complete | #400 |

### AI Query Endpoints

| Component | Description | Status |
|-----------|-------------|--------|
| `TransactionQueryTool` | Natural language transaction queries | ✅ |
| `BalanceQueryTool` | Multi-currency balance aggregation | ✅ |
| `PatternAnalysisTool` | Spending pattern detection | ✅ |
| API Endpoints | `/api/ai/query/transactions`, `/balances`, `/patterns` | ✅ |
| MCP Tools | Model Context Protocol integration | ✅ |

### RegTech Adapters & Services

| Component | Description | Status |
|-----------|-------------|--------|
| `FinCENAdapter` | US BSA E-Filing (CTR, SAR, CMIR, FBAR) | ✅ |
| `ESMAAdapter` | EU FIRDS/TREM (MiFID, EMIR, SFTR) | ✅ |
| `FCAAdapter` | UK Gabriel (MiFID, REP-CRIM, SUP16) | ✅ |
| `MASAdapter` | SG eServices Gateway (MAS Returns, STR) | ✅ |
| `MifidReportingService` | Transaction reporting (RTS 25), best execution (RTS 27/28) | ✅ |
| `MicaComplianceService` | CASP authorization, whitepaper validation, reserves | ✅ |
| `TravelRuleService` | FATF Rec 16, jurisdiction thresholds | ✅ |
| RegTech API | 11 endpoints under `/api/regtech` | ✅ |

### Scope Decisions

| Item | Decision |
|------|----------|
| ML Anomaly Detection | Deferred to v2.9.0 (requires behavioral profiling DB schema) |
| BaaS Implementation | Deferred to v2.9.0 (SDK generation + partner metering) |
| Production Hardening | Deferred to v2.9.0 (smart contracts, ZK circuits, HSM) |

---

## Version 2.9.0 - BaaS & Production Hardening ✅ RELEASED

**Release Date**: February 10, 2026
**Theme**: Banking-as-a-Service + Production Readiness

### Phase 1: ML Anomaly Detection ✅ COMPLETE

| Component | Description | Status |
|-----------|-------------|--------|
| `StatisticalAnomalyActivity` | Z-score, IQR-based detection | ✅ |
| `BehavioralProfileActivity` | User baseline comparison | ✅ |
| `VelocityAnomalyActivity` | Transaction frequency analysis | ✅ |
| `GeolocationAnomalyActivity` | Location-based anomalies | ✅ |
| Database | `user_behavioral_profiles`, `anomaly_detections` | ✅ |

### Phase 2: BaaS Implementation ✅ COMPLETE

| Component | Description | Status | PR |
|-----------|-------------|--------|-----|
| `PartnerUsageMeteringService` | API usage tracking + auth middleware | ✅ | #429 |
| `PartnerBillingService` | Invoice generation with overage + discounts | ✅ | #430 |
| `SdkGeneratorService` | Auto-generate TypeScript, Python, Java, Go, PHP SDKs | ✅ | #431 |
| `EmbeddableWidgetService` | Payment, Checkout, Balance, Transfer, Account widgets | ✅ | #432 |
| `PartnerMarketplaceService` | Integration connectors + `PartnerIntegration` model | ✅ | #433 |
| Partner API Controllers | 5 controllers, 26 endpoints under `/api/partner/v1` | ✅ | #434 |
| Integration Tests | End-to-end BaaS workflow tests | ✅ | #435 |

### Phase 3: Production Hardening ✅ COMPLETE (v2.9.1)

| Component | Description | Status | PR |
|-----------|-------------|--------|-----|
| On-Chain SBT | ERC-5192 Soulbound Token on Polygon via JSON-RPC | ✅ | #441 |
| ZK Circuits | SnarkjsProverService, PoseidonHasher, ProductionMerkleTreeService | ✅ | #442 |
| HSM Providers | AWS KMS + Azure Key Vault providers with HsmProviderFactory | ✅ | #443 |
| Security Audit | `php artisan security:audit` with 8 OWASP checks | ✅ | #444 |

---

## Version 2.9.1 - Production Hardening ✅ RELEASED

**Release Date**: February 10, 2026
**Theme**: Production-grade implementations for smart contracts, ZK circuits, HSM, and security

### Delivered

| Feature | Description | PR |
|---------|-------------|-----|
| On-Chain SBT | ERC-5192 Soulbound Token minting/revoking on Polygon, opt-in via config | #441 |
| snarkjs Integration | SnarkjsProverService wraps CLI for ZK proof generation, PoseidonHasher for Merkle hashing | #442 |
| AWS KMS & Azure Key Vault | AwsKmsHsmProvider + AzureKeyVaultHsmProvider implementing HsmProviderInterface | #443 |
| Security Audit Tooling | `php artisan security:audit` command with 8 OWASP Top 10 automated checks | #444 |

---

## Version 2.10.0 - Mobile API Compatibility ✅ RELEASED

**Release Date**: February 10, 2026
**Theme**: Mobile-Facing API Endpoints & Response Consistency

### Delivered Features

| Feature | Description | Status |
|---------|-------------|--------|
| Mobile Commerce API | Merchant listings, QR code parsing/generation, payment requests, payment processing | ✅ |
| Mobile Relayer API | Relayer status, gas estimation, UserOp building/submission/tracking, paymaster data | ✅ |
| Mobile Wallet API | Token list, balances, addresses, wallet state, transaction history, send flow | ✅ |
| Mobile TrustCert API | Trust level status, requirements, limits, certificate application CRUD | ✅ |
| Auth Compatibility | Response envelope wrapping, /auth/me alias, account deletion, passkey registration | ✅ |
| CORS Headers | X-Client-Platform and X-Client-Version headers allowed | ✅ |
| Handover Documentation | Mobile API compatibility handover document (docs/MOBILE_API_COMPATIBILITY.md) | ✅ |

### Summary

Adds approximately 30 new mobile-facing API endpoints across wallet, TrustCert, commerce, and relayer domains. Ensures response envelope consistency (`{ success, data }`) for mobile client consumption. Includes comprehensive handover documentation for frontend integration.

---

## Version 3.0.0 - Cross-Chain & DeFi ✅ COMPLETED

**Release Date**: February 10, 2026
**GitHub Release**: https://github.com/FinAegis/core-banking-prototype-laravel/releases/tag/v3.0.0
**Theme**: Cross-Chain Bridges & DeFi Protocol Integration

### Delivered Features

| Feature | Status | PRs |
|---------|--------|-----|
| CrossChain Domain (Bridge Protocols) | ✅ Complete | #454 |
| DeFi Domain (DEX & Lending Connectors) | ✅ Complete | #454 |
| Code Review Fixes | ✅ Complete | #455 |

### CrossChain Domain

| Component | Description | Status |
|-----------|-------------|--------|
| `BridgeOrchestratorService` | Multi-provider bridge orchestration (Wormhole, LayerZero, Axelar) | ✅ |
| `BridgeFeeComparisonService` | Cross-provider fee/time comparison with weighted ranking | ✅ |
| `CrossChainAssetRegistryService` | Token address mapping across 9 chains | ✅ |
| `BridgeTransactionTracker` | Cache-based bridge transaction lifecycle tracking | ✅ |
| `CrossChainSwapService` | Atomic cross-chain swaps (bridge + swap in optimal order) | ✅ |
| `CrossChainSwapSaga` | Compensation-based saga for bridge+swap failure recovery | ✅ |
| `CrossChainYieldService` | Best yield discovery across chains with bridge cost analysis | ✅ |
| `MultiChainPortfolioService` | Aggregated portfolio across all chains with DeFi positions | ✅ |

### DeFi Domain

| Component | Description | Status |
|-----------|-------------|--------|
| `UniswapV3Connector` | Multi-fee-tier swaps, L2 gas optimization, price impact estimation | ✅ |
| `AaveV3Connector` | Supply/borrow/repay/withdraw with market data and health factor | ✅ |
| `CurveConnector` | Stablecoin-optimized swaps with lower fees (0.04%) | ✅ |
| `LidoConnector` | ETH staking with stETH derivatives and withdrawal queue | ✅ |
| `SwapAggregatorService` | Multi-DEX quote aggregation with best-price routing | ✅ |
| `SwapRouterService` | Optimal route selection across DEXs with price impact validation | ✅ |
| `FlashLoanService` | Aave V3 flash loan orchestration with 0.05% fee | ✅ |
| `DeFiPortfolioService` | Aggregated portfolio with protocol/chain/type breakdowns | ✅ |
| `DeFiPositionTrackerService` | DeFi position tracking with health factor monitoring | ✅ |

### API Endpoints (v3.0.0)

| Category | Endpoints |
|----------|-----------|
| CrossChain | `GET /chains`, `POST /bridge/quote`, `POST /bridge/initiate`, `GET /bridge/{id}/status`, `POST /swap/quote`, `POST /swap/execute` |
| DeFi | `GET /protocols`, `POST /swap/quote`, `POST /swap/execute`, `GET /lending/markets`, `GET /portfolio`, `GET /positions`, `POST /staking/stake`, `GET /yield/best` |

---

## Version 3.1.0 - Consolidation, Documentation & UI Completeness ✅ COMPLETED

**Target**: February 2026
**Theme**: Consolidation, Documentation & UI Completeness

### Context

After 18 releases (v1.1.0 → v3.0.0), the platform has grown to 41 domains, 266+ services, 167 controllers, and 1,150+ routes. v3.1.0 focuses on filling gaps in documentation, admin UI, user-facing UI, and internal docs to match the feature set.

### Plan: 8 Phases

| Phase | Description | Status | PR |
|-------|-------------|--------|-----|
| 1. Internal Docs & Housekeeping | VERSION_ROADMAP, ARCHITECTURAL_ROADMAP, Serena memories, git hygiene | ✅ | #456 |
| 2. Swagger/OpenAPI Documentation | Fix L5-Swagger config, add @OA annotations to undocumented controllers | ✅ | #457, #458 |
| 3. Website Feature Pages | Landing page update, 7 new feature pages for v2.0+ features | ✅ | #459 |
| 4. Developer Portal | Update all 6 developer portal pages with v2.0+ API areas | ✅ | #460, #461 |
| 5. Admin UI Phase 1 | Filament resources for CrossChain, DeFi, RegTech, Fraud, Wallet, Treasury, Lending | ✅ | #462 |
| 6. Admin UI Phase 2 | Filament resources for Privacy, Commerce, TrustCert, KeyMgmt, Relayer, MobilePayment, Mobile, Partner | ✅ | #463 |
| 7. User UI | Blade views for cross-chain, DeFi, privacy, trust certificates | ✅ | #464 |
| 8. Quality & Forward Planning | CHANGELOG, roadmap update, Serena memory updates | ✅ | #465 |

### Deliverables Summary

| Category | Count | Details |
|----------|-------|---------|
| Swagger Annotations | ~80 routes | CrossChain, DeFi, RegTech, MobilePayment, Partner, AI controllers |
| Website Feature Pages | 7 new | crosschain-defi, privacy-identity, mobile-payments, regtech, baas, ai, multi-tenancy |
| Developer Portal Pages | 6 updated | index, api-docs, examples, sdks, webhooks, postman |
| Filament Admin Resources | 15 new | Covering 15 previously-unrepresented domains |
| User-Facing Views | 4 new | crosschain, defi, privacy, trustcert |
| New Eloquent Models | 3 | BridgeTransaction, DeFiPosition, Certificate |
| New Migrations | 3 | bridge_transactions, defi_positions, certificates |

---

## v3.2.0 — Production Readiness & Plugin Architecture ✅ COMPLETED

**Released**: February 11, 2026
**Theme**: Open-Source Readiness, Plugin System, Performance

### Delivered (6 Phases, PRs #466-#470)

| Phase | Branch | Deliverables |
|-------|--------|-------------|
| 1 | `feature/v3.2.0-module-manifests` | 12 new module.json manifests, enable/disable commands, config/modules.php |
| 2 | `feature/v3.2.0-modular-routes` | ModuleRouteLoader, 24 per-domain route files, api.php reduced from 1,646 to ~240 lines |
| 3 | `feature/v3.2.0-module-admin` | ModuleController REST API, Filament Modules page, ModuleHealthWidget |
| 4 | `feature/v3.2.0-performance` | k6 load tests (smoke/load/stress), QueryPerformanceMiddleware, performance:report command |
| 5 | `feature/v3.2.0-open-source` | Dependabot, issue/PR templates, SPDX headers, README/CONTRIBUTING updates |
| 6 | `chore/v3.2.0-release` | Integration tests, CHANGELOG, release documentation |

---

## v3.3.0 — Event Store Optimization & Observability ✅ COMPLETED

**Released**: February 12, 2026
**Theme**: Production Operations Tooling

### Delivered (6 Phases, PRs #493-#498)

| Phase | Branch | Deliverables |
|-------|--------|-------------|
| 1 | `feature/v3.3.0-event-store-commands` | EventStoreService, event:stats/replay/rebuild commands, snapshot:cleanup |
| 2 | `feature/v3.3.0-observability-dashboards` | EventStoreDashboard Filament page, 4 widgets, MonitoringMetricsUpdated broadcast |
| 3 | `feature/v3.3.0-structured-logging` | StructuredJsonFormatter, StructuredLoggingMiddleware, LogsWithDomainContext trait |
| 4 | `feature/v3.3.0-deep-health-checks` | EventStoreHealthCheck service, checkDeep/checkDomain on HealthChecker, --deep flag, DomainHealthWidget |
| 5 | `feature/v3.3.0-event-store-partitioning` | EventArchivalService, event:archive/compact commands, archived_events table, event-store config |
| 6 | `feature/v3.3.0-release` | 3 integration test suites, CHANGELOG, documentation updates |

---

## v3.4.0 — API Maturity & Developer Experience ✅ COMPLETED

**Released**: February 12, 2026
**Theme**: API Polish & SDK Ecosystem

### Delivered (PRs #499-#510)

| Feature | Status | Description |
|---------|--------|-------------|
| Formal API Versioning | ✅ | v1/v2/v3 strategy with deprecation headers and sunset dates |
| Rate Limiting per Tier | ✅ | Partner tier-based rate limiting (Starter/Growth/Enterprise) |
| SDK Auto-Generation CI | ✅ | Automated SDK builds on release (TypeScript, Python, Go) |
| OpenAPI 143+ Endpoints | ✅ | Swagger annotations for 143+ endpoints across all domains |

---

## v3.5.0 — Compliance Certification Readiness ✅ COMPLETED

**Released**: February 13, 2026
**Theme**: Enterprise Compliance & Security

### Delivered (4 Phases, PRs #511-#516)

| Feature | Status | Description |
|---------|--------|-------------|
| SOC 2 Type II Preparation | ✅ | Audit trail, access controls, evidence collection, 14 control families |
| PCI DSS Readiness | ✅ | Cardholder data isolation, encryption, 12 requirement assessments |
| Multi-Region Deployment | ✅ | Geographic distribution, data residency, region health monitoring |
| GDPR Enhanced Compliance | ✅ | Article 30 ROPA, DPIA, breach notification, consent v2, retention policies |

---

## v4.0.0 — Architecture Evolution ✅ COMPLETED

**Released**: February 13, 2026
**Theme**: Event Store v2, GraphQL API, Plugin Marketplace

### Delivered (7 Phases, PRs #517-#523)

| Feature | Status | Description |
|---------|--------|-------------|
| Event Store v2 — Domain Tables | ✅ | EventRouter for namespace-based domain table routing (21 domains) |
| Event Store v2 — Migration Tooling | ✅ | Batch migration from shared to domain tables with validation |
| Event Store v2 — Versioning & Upcasting | ✅ | Schema evolution with chained upcasters (v1→v2→v3) |
| GraphQL API — Foundation | ✅ | Lighthouse-PHP integration, Account domain, custom @tenant directive |
| GraphQL API — Core Domains | ✅ | Wallet, Exchange, Compliance schemas, DataLoaders, subscriptions |
| Plugin Marketplace — Foundation | ✅ | PluginManager, dependency resolver, 6 Artisan commands, scaffold generator |
| Plugin Marketplace — Sandboxing | ✅ | Permission system, security scanner, marketplace API, Filament admin |

---

## v4.1.0 — GraphQL Expansion + Platform Hardening ✅ COMPLETED

**Released**: February 13, 2026
**Theme**: GraphQL Coverage Expansion, Event Replay Filtering, Projector Health Monitoring

### Delivered

| Feature | Status | Description |
|---------|--------|-------------|
| GraphQL — Treasury Domain | ✅ | AssetAllocation type, portfolio queries, createPortfolio/rebalancePortfolio mutations |
| GraphQL — Payment Domain | ✅ | PaymentTransaction type, payment queries, initiatePayment mutation |
| GraphQL — Lending Domain | ✅ | LoanApplication type, loan queries, applyForLoan/approveLoan mutations |
| GraphQL — Stablecoin Domain | ✅ | StablecoinReserve type, reserve queries, mintStablecoin/redeemStablecoin mutations |
| GraphQL — CrossChain Domain | ✅ | BridgeTransaction type, bridge queries, initiateBridgeTransfer mutation |
| GraphQL — DeFi Domain | ✅ | DeFiPosition type, position queries, openPosition/closePosition mutations |
| Event Replay Filtering | ✅ | --event-type and --aggregate-id filter options for selective replay |
| Projector Health Monitoring | ✅ | ProjectorHealthService, projector:health command, REST endpoint |
| Integration Tests | ✅ | 8 test files covering all new GraphQL domains, event replay, projector health |

**GraphQL Coverage**: 10/41 domains (up from 4/41 in v4.0.0)

---

## v4.2.0 — Real-time Platform + Plugin Ecosystem ✅ COMPLETED

**Released**: February 13, 2026
**Theme**: Real-time Subscriptions, Plugin Hook System, Core Mutations

### Delivered

| Feature | Status | Description |
|---------|--------|-------------|
| GraphQL Subscriptions | ✅ | 4 new subscriptions (orderMatched, portfolioRebalanced, paymentStatusChanged, bridgeTransferCompleted) |
| Plugin Hook System | ✅ | PluginHookInterface contract, PluginHookManager with priority dispatch, 17 hook points |
| Example Plugins | ✅ | Webhook Notifier (HMAC-signed HTTP webhooks), Audit Exporter (JSON/CSV export) |
| Core Domain Mutations | ✅ | 8 new mutations: freeze/unfreeze account, create wallet, transfer funds, place/cancel order, submit KYC, trigger AML |

---

## v4.3.0 — Developer Experience + Extended GraphQL ✅ COMPLETED

**Released**: February 13, 2026
**Theme**: Developer Tools, GraphQL Security, Extended Domain Coverage

### Delivered

| Feature | Status | Description |
|---------|--------|-------------|
| GraphQL — Fraud Domain | ✅ | FraudCase type, queries, escalateFraudCase mutation |
| GraphQL — Mobile Domain | ✅ | MobileDevice type, device queries |
| GraphQL — MobilePayment Domain | ✅ | PaymentIntent type, queries, createPaymentIntent mutation |
| GraphQL — TrustCert Domain | ✅ | Certificate type, queries |
| Dashboard Widget Plugin | ✅ | Filament StatsOverviewWidget with cached domain health counts |
| CLI Commands | ✅ | graphql:schema-check, plugin:verify, domain:status |
| GraphQL Security | ✅ | Rate limiting middleware, query cost analysis, introspection control |

**GraphQL Coverage**: 14/41 domains (up from 10/41 in v4.1.0; later expanded to 33/41 in v5.0.0)

---

## v5.0.0 — Streaming Architecture + API Gateway (MAJOR) ✅ COMPLETED

**Released**: February 13, 2026
**Theme**: Event Streaming, Live Dashboard, Notification System, API Gateway

### Delivered

| Feature | Status | Description |
|---------|--------|-------------|
| Event Streaming Foundation | ✅ | EventStreamPublisher (Redis Streams XADD, batch publish, MAXLEN trimming), EventStreamConsumer (XREADGROUP, XACK, XAUTOCLAIM, consumer groups) |
| Event Streaming Config | ✅ | 15 domain stream mappings, retention policy, consumer group settings |
| Event Stream Monitor | ✅ | `event-stream:monitor` command with --domain filter and --json output |
| Live Dashboard | ✅ | LiveMetricsService (domain health, event throughput, stream status, projector lag), 5 REST endpoints |
| Notification System | ✅ | Multi-channel (email, push, in-app, webhook, SMS), pluggable handlers, batch queue/flush, 7 event triggers |
| API Gateway | ✅ | ApiGatewayMiddleware with X-Request-Id tracing, timing, version headers |
| GraphQL Schema Expansion | ✅ | 10 new domain schemas (Custodian, KeyManagement, Banking, Commerce, Asset, RegTech, AI, Governance, Privacy, Relayer), bringing total to 24 domains; later expanded to 33 domains with AgentProtocol, Basket, Batch, CardIssuance, Cgo, FinancialInstitution, Product, Regulatory, User |
| Tests | ✅ | 29+ new tests across 6+ test files |

### Breaking Changes
- **MAJOR version**: Introduces streaming architecture patterns
- Redis Streams dependency for event streaming (requires Redis 5.0+)

---

## v5.0.1 — Platform Hardening + Documentation Refresh ✅ COMPLETED

**Released**: February 13, 2026
**Theme**: GraphQL CQRS Alignment, OpenAPI Coverage, DX Improvements, Documentation Accuracy

### Delivered

| Feature | Status | Description |
|---------|--------|-------------|
| GraphQL CQRS Alignment | ✅ | All 21 GraphQL mutations refactored from direct Eloquent to WorkflowStub/Service patterns |
| OpenAPI 100% Coverage | ✅ | `@OA` annotations added to 52 remaining controllers (143+ total endpoints) |
| Plugin Marketplace UI | ✅ | Filament admin page with search, filter, enable/disable, security scan |
| PHP 8.4 CI Upgrade | ✅ | 10 workflow files + composer.json updated from PHP 8.3 to 8.4 |
| Structural Test Conversion | ✅ | 97 test files converted from class_exists stubs to ReflectionClass assertions |
| Documentation Refresh | ✅ | 12+ docs files updated, GraphQL count 14→24→33 across docs |
| Website Updates | ✅ | Sub-products, SDKs, feature pages, prototype disclaimers |

---

## v5.1.0 — Mobile API Completeness & GraphQL Full Coverage ✅ COMPLETED

**Released**: February 16, 2026
**Theme**: Mobile Integration Readiness, GraphQL 33-Domain Coverage, CI/CD Hardening

### Delivered

| Feature | Status | Description |
|---------|--------|-------------|
| Mobile API Endpoints | ✅ | 21 missing endpoints across Privacy (11), Commerce (4), Card Issuance (3), Mobile (2), Wallet (1) |
| GraphQL Full Coverage | ✅ | 9 remaining domain schemas added, completing 33-domain coverage |
| GraphQL Integration Tests | ✅ | 14-domain integration test suite |
| Blockchain Models | ✅ | BlockchainAddress/Transaction Eloquent models with UUID, migration, controller |
| Test Quality | ✅ | 42 new feature tests, 9 pre-existing failures fixed, behavioral test conversions |
| CI Hardening | ✅ | k6 non-blocking, per-scenario thresholds, PHPStan bootstrap, PHPCS fixes |
| Security | ✅ | axios CVE fix, PHPStan generic types, MariaDB timestamp fixes |

---

## v5.1.1 — Mobile App Landing Page ✅ COMPLETED

**Released**: February 16, 2026
**Theme**: Mobile App Teaser, CI Test Stability

### Delivered

| Feature | Status | Description |
|---------|--------|-------------|
| Mobile App Landing Page | ✅ | Futuristic dark-theme page at `/app` with email signup, feature cards, Shamir's SSS explainer, platform architecture section, FAQ |
| Azure HSM Test Fix | ✅ | Resolved flaky OAuth token caching test — race condition with parallel Redis in CI |

---

## v5.1.4 — Refresh Token Mechanism ✅ COMPLETED

**Released**: February 18, 2026
**Theme**: Proper Access/Refresh Token Pairs, Tech Debt, Documentation

### Delivered

| Feature | Status | Description |
|---------|--------|-------------|
| Refresh token mechanism | ✅ | Access tokens (short-lived, role-based) paired with refresh tokens (`['refresh']` ability, 30-day default) via Sanctum `abilities` column — no DB migration |
| Token rotation | ✅ | `POST /api/auth/refresh` revokes old access+refresh pair, issues new pair; prevents replay attacks |
| Public refresh endpoint | ✅ | `/refresh` route moved out of `auth:sanctum` middleware — works after access tokens expire; accepts token via body or `Authorization: Bearer` |
| Auth response enrichment | ✅ | `refresh_token` and `refresh_expires_in` in login, register, passkey auth, and refresh responses |
| Session limit fix | ✅ | `enforceSessionLimits()` now excludes refresh tokens from concurrent session count |
| PHPStan config fix | ✅ | `config/sanctum.php` `explode()` type error resolved with `(string)` cast |
| OpenAPI docs update | ✅ | Swagger annotations for login and register endpoints now include `refresh_token` and `refresh_expires_in` |
| Security tests | ✅ | 5 new tests: refresh after expiry, reject access tokens for refresh, reject expired refresh tokens, token rotation, missing token |

---

## v5.1.3 — Mobile API Compatibility ✅ COMPLETED

**Released**: February 17, 2026
**Theme**: Mobile Onboarding Fixes, Auth Response Standardization, Token Refresh

### Delivered

| Feature | Status | Description |
|---------|--------|-------------|
| Optional `owner_address` | ✅ | `POST /api/v1/relayer/account` no longer requires `owner_address` — derives deterministic address from authenticated user during onboarding |
| Auth response standardization | ✅ | Register endpoint now returns standard `{ success, data }` envelope with full User model, matching login format |
| Token refresh endpoint | ✅ | `POST /api/auth/refresh` implemented — revokes current token, issues new one with fresh expiration |
| Logout-all endpoint | ✅ | `POST /api/auth/logout-all` implemented — revokes all tokens across all devices |
| Passkey auth response | ✅ | `authenticate` now returns `user` object and `expires_in` for consistent mobile session handling |
| Rate limiter crash fix | ✅ | `TransactionRateLimitMiddleware.incrementCounters()` no longer crashes on unknown transaction types (`relayer`) |

---

## v5.1.2 — Production Landing Page Fix ✅ COMPLETED

**Released**: February 16, 2026
**Theme**: Production CSS Fix, CSP Compliance

### Delivered

| Feature | Status | Description |
|---------|--------|-------------|
| Standalone Tailwind CSS | ✅ | Pre-compiled `public/css/app-landing.css` via Tailwind CLI — no Vite dependency |
| CSP Compliance | ✅ | Self-hosted CSS instead of CDN script — no CSP `script-src` changes needed |

### Root Cause
The `/app` landing page rendered correctly locally but broke in production because `public/build/` is gitignored. The Vite-compiled CSS on production was built before `app.blade.php` existed, so Tailwind purged all its utility classes. Initial CDN fix was blocked by Content Security Policy. Final solution: pre-compiled standalone CSS committed to git.

## v5.5.0 — Production Relayer & Card Webhooks ✅ COMPLETED

**Released**: February 21, 2026

### Delivered

| Feature | Status | Description |
|---------|--------|-------------|
| ERC-4337 Pimlico v2 | ✅ | Production bundler, paymaster, smart account factory integration |
| Marqeta Webhook Auth | ✅ | Basic Auth + HMAC signature verification for card webhooks |
| .env.zelta.example | ✅ | Full production environment template synced |
| Platform Hardening | ✅ | IdempotencyMiddleware, E2E banking tests, multi-tenancy isolation |

---

## v5.6.0 — RAILGUN Privacy Protocol ✅ COMPLETED

**Released**: February 28, 2026

### Delivered

| Feature | Status | Description |
|---------|--------|-------------|
| RailgunBridgeClient | ✅ | HTTP client for Node.js RAILGUN bridge service |
| RailgunMerkleTreeService | ✅ | Implements MerkleTreeServiceInterface via bridge |
| RailgunZkProverService | ✅ | Implements ZkProverInterface via bridge |
| RailgunPrivacyService | ✅ | Orchestrator for shield/unshield/transfer flows |
| RailgunWallet Model | ✅ | Encrypted wallet data per user with UUID keys |
| ShieldedBalance Model | ✅ | Cached shielded token balances per network |
| 4-Chain Support | ✅ | Ethereum, Polygon, Arbitrum, BSC (Base not supported by RAILGUN) |
| 57 Tests | ✅ | Unit and feature tests with Http::fake() bridge mocking |

---

## v5.7.0 — Mobile Rewards & Security Hardening ✅ COMPLETED

**Released**: February 28, 2026

### Delivered

| Feature | Status | Description |
|---------|--------|-------------|
| Rewards Domain | ✅ | Complete gamification: quests, XP/levels, points shop, daily streaks |
| Race-Safe Operations | ✅ | DB::transaction() + lockForUpdate() for quest completion and redemption |
| WebAuthn Hardening | ✅ | rpIdHash, UP/UV flags, COSE alg/curve validation, origin check |
| Recent Recipients | ✅ | Deduplicated send history endpoint with limit parameter |
| Notification Unread Count | ✅ | Badge count endpoint for mobile home screen |
| Route Aliases | ✅ | Mobile-friendly v1 paths for create-account, estimate-fee, data-export |
| Error Code Specificity | ✅ | QUEST_NOT_FOUND, QUEST_ALREADY_COMPLETED, ITEM_OUT_OF_STOCK, etc. |
| 44 Feature Tests | ✅ | Full coverage including edge cases and race conditions |

### Breaking Changes (Mobile)
- Registration challenge: `POST /api/auth/passkey/challenge` with `{type: 'registration'}` → `POST /api/v1/auth/passkey/register-challenge`
- GET challenge route removed (security — challenges are POST-only)
- Estimate fee alias changed from GET to POST

---

---

## v5.8.0 — Mobile Go-Live ✅ COMPLETED

**Released**: March 1, 2026

### Delivered

| Feature | Status | Description |
|---------|--------|-------------|
| Rewards GraphQL | ✅ | 35th GraphQL domain schema |
| Rewards Admin | ✅ | Filament admin resources for quests, XP, shop, streaks |
| OpenAPI Attributes | ✅ | Documentation schemas + RewardsController migrated to PHP 8 attributes |
| Mobile v5.7.1 hotfix | ✅ | Handover items #2, #4, #7 resolved |
| Pimlico Bundler | ✅ | Real ERC-4337 submission, receipt query, config-driven tokens |
| Marqeta Cards | ✅ | Card listing + transactions via Marqeta adapter |
| DB Merchants | ✅ | Commerce merchants backed by database with search/pagination |
| Chainalysis Sanctions | ✅ | Sanctions screening endpoint via Chainalysis adapter |
| Recovery Shard Backup | ✅ | Cloud backup CRUD endpoints for Shamir key recovery |
| WebSocket Channels | ✅ | 4 mobile-aligned channels (privacy, commerce, trustcert, user) |
| Privacy Calldata | ✅ | Encrypted calldata persistence, dual-lookup retrieval, tx-hash update |

### Key Details
- PRs #670-#677 (7 feature PRs + 1 release doc PR)
- All 13 mobile go-live items resolved (11 code + SSL deferred + env ops)
- `privacy_transactions` table with AES-256 encrypted calldata
- `PrivacyTransaction` model with UUID PK, user scopes, dual-lookup (tx_hash/UUID)
- 21+ new tests for privacy calldata

---

## v5.9.0 — OpenAPI Migration & Security Hardening ✅ COMPLETED

**Released**: March 1, 2026

### Delivered

| Feature | Status | Description |
|---------|--------|-------------|
| Token Expiration Enforcement | ✅ | Global `CheckTokenExpiration` middleware in api group |
| Scope-Based Authorization | ✅ | `EnforceMethodScope` middleware: GET→read, POST/PUT/PATCH→write, DELETE→delete |
| OpenBanking Cleanup | ✅ | 501 stubs replaced with 503 Service Unavailable |
| OpenAPI PHP 8 Migration | ✅ | 173 files migrated from `@OA\` docblocks to `#[OA\]` attributes |
| Doctrine Annotations Removed | ✅ | `doctrine/annotations` dependency removed from composer.json |
| WebAuthn COSE Hardening | ✅ | Fixed null bypass in algorithm/curve validation, removed unsupported RS256 |
| SSL Pinning Endpoint | ✅ | `GET /api/v1/mobile/ssl-pins` for certificate pinning |
| GDPR Async Export | ✅ | 202 Accepted + `ProcessGdprDataExport` job + status polling |
| Notification WebSocket | ✅ | `NotificationCountUpdated` broadcast on `user.{userId}` channel |

### Key Details
- PRs #679-#683 (5 PRs: 3 security + 1 migration + 1 mobile feedback)
- Phase 1: Security Hardening — global token expiration, method-based scope enforcement, OpenBanking stub cleanup
- Phase 2: OpenAPI Migration — custom `bin/migrate-openapi-v2.php` script, batch conversion, `doctrine/annotations` removed
- Phase 3: Mobile Feedback — WebAuthn COSE fixes, SSL pinning, GDPR async, notification real-time count
- Mobile developer feedback triaged: 4 items fixed, 4 already resolved in v5.8.0, 4 not actionable

---

## Version 5.10.0 - Performance Wiring & API Maturity ✅ RELEASED

**Release Date**: March 2, 2026
**Theme**: Performance Wiring & API Maturity

### Summary

Wire existing observability infrastructure to production routes and improve API maturity with standardized error responses and RFC 8594 deprecation headers.

### Delivered Features

| Feature | Status | Details |
|---------|--------|---------|
| Observability Middleware Wiring | ✅ | 5 middleware applied to API group: StructuredLogging, Metrics, QueryPerformance, CachePerformance, Tracing |
| Middleware Aliases | ✅ | Register `metrics`, `cache.performance`, `tracing` aliases |
| Standardized Error Responses | ✅ | All API errors include `error` code + `request_id` fields |
| Error Code Mapping | ✅ | `VALIDATION_ERROR`, `UNAUTHENTICATED`, `FORBIDDEN`, `NOT_FOUND`, `RATE_LIMITED`, `SERVER_ERROR` |
| RFC 8594 Deprecation Headers | ✅ | `Deprecation`, `Sunset`, `Link` headers on legacy endpoints |
| Legacy Route Tagging | ✅ | `/api/profile` and `/api/kyc/documents` sunset 2026-09-01 |
| Integration Tests | ✅ | 11 middleware integration tests + 5 error response tests + 8 deprecation tests |

### Key Details
- PRs #691-#694 (4 PRs)
- Phase 1: Wire 5 existing observability middleware to API route group
- Phase 2: Add comprehensive integration tests for middleware stack
- Phase 3: Standardize API error responses with semantic error codes and request_id
- Phase 4: RFC 8594 deprecation headers for legacy API endpoints

---

## v5.11.0 — Mobile API Maturity & Website Launch ✅ COMPLETED

**Released**: March 5, 2026

### Delivered

| Feature | Status | Description |
|---------|--------|-------------|
| GDPR Export Download | ✅ | Secure download endpoint + recovery shard blob storage |
| Mobile API Fixes | ✅ | 7 items from handoff audit resolved |
| Website Production Launch | ✅ | `SHOW_PROMO_PAGES` env flag for production visibility |
| Homepage Content Refresh | ✅ | Updated for v5.11.0 |
| App Landing Refresh | ✅ | Developer hub, feature pages content refresh |

---

## v5.12.0 — Design System v2, Onramper, CI Pipeline Green ✅ COMPLETED

**Released**: March 10, 2026

### Delivered

| Feature | Status | Description |
|---------|--------|-------------|
| Notifications API | ✅ | Paginated list with type filtering |
| Banners API | ✅ | Admin panel for promotional carousel |
| Gas Sponsorship | ✅ | Free user transaction gas sponsorship service |
| Referral System | ✅ | Code generation, sponsorship rewards, KYC-triggered |
| On/Off Ramp API | ✅ | Provider-agnostic Onramper integration with session management |
| Foodo Insights | ✅ | Restaurant analytics dashboard |
| Frontend Design Overhaul | ✅ | Comprehensive design system v2, feature hero dark design |
| Investor Feedback Polish | ✅ | 3 refactor PRs: mobile UX, security, configurable copy |

---

## v5.13.0 — Zelta Rebrand & Landing Page ✅ COMPLETED

**Released**: March 15, 2026

### Delivered

| Feature | Status | Description |
|---------|--------|-------------|
| Zelta Landing Page | ✅ | Neo-brutalist design: mint/lavender gradients, Space Grotesk fonts, 3px borders |
| Landing Page Refactor | ✅ | DRY brutalist utilities, ARIA accessibility, vanilla JS |
| CSP Fix | ✅ | Compiled Tailwind CSS (14KB) replacing CDN, GA CSP fix |
| SEO Metadata | ✅ | "Agentic Payments" title/description/keywords, JSON-LD schemas |
| OG Images | ✅ | New 1200x630 and 1024x512 images with card visuals |
| Email Branding | ✅ | 7 mail classes: `config('brand.name')` instead of hardcoded |
| Favicon Rebrand | ✅ | 14 icon files: Z in mint neo-brutalist style |
| Brand Config | ✅ | Defaults updated to Zelta, `@zelta.app` emails |
| SchemaHelper | ✅ | Dynamic `config('brand.name')` in all schemas |
| Footer/API/Docs | ✅ | Dynamic brand in footer copyright, OpenAPI, PublicAPI |

---

## v5.14.0 — RPC Optimization & WebSocket Balance Events ✅ COMPLETED

**Released**: March 15, 2026
**Theme**: Eliminate polling, reduce Alchemy RPC costs by 90%+

### Problem
Mobile app polling `wallet/balances` + `wallet/state` every 60s generated ~98K Alchemy RPC calls/day from a single device. `eth_blockNumber` and `eth_gasPrice` had zero caching. `MobileWalletController` type-hinted the concrete `WalletBalanceService` instead of the interface, bypassing the demo/production service swap entirely.

### Delivered

| Feature | Status | Description |
|---------|--------|-------------|
| RPC Call Caching | ✅ | Cache `getBlockNumber`, `getGasPrice`, `getMaxPriorityFeePerGas` with 15s TTL |
| Balance Cache TTL | ✅ | Increased from 30s → 120s (configurable via `BALANCE_CACHE_TTL`) |
| Interface Fix | ✅ | `MobileWalletController` now uses `WalletBalanceProviderInterface` — demo/prod swap works |
| `wallet.balance_updated` Event | ✅ | Broadcast on `private-wallet.{userId}` when token transfer detected |
| `wallet.state_changed` Event | ✅ | Broadcast when smart account created/deployed |
| `privacy.balance_updated` Event | ✅ | Broadcast when shield/unshield/transfer completes |
| Alchemy Webhook Controller | ✅ | `POST /api/webhooks/alchemy/address-activity` with HMAC-SHA256 verification |
| Channel Authorization | ✅ | `private-wallet.{userId}` registered in channels.php |

### Key Details
- PRs #752-#753
- Expected impact: ~90% reduction in Alchemy RPC calls (98K → ~10K/day)
- Mobile PR #260 (client side) fully compatible
- New env vars: `RPC_CACHE_TTL`, `BALANCE_CACHE_TTL`, `ALCHEMY_WEBHOOK_SIGNING_KEY`

---

## v6.0.0 — Platform Completeness & Developer Ecosystem ✅ RELEASED

**Released**: March 17, 2026
**Theme**: Complete all domains, launch plugin ecosystem, extend developer portal

### Domain Completion Audit (March 15, 2026)

**43 domains total — 38 production-ready, 5 need attention:**

| Domain | Current | Target | Gap |
|--------|---------|--------|-----|
| Activity | ~30% | 80% | Expand beyond basic audit log — add event timeline, user activity feeds |
| Contact | ~30% | 70% | Add auto-responders, ticket assignment, status tracking |
| Newsletter | ~40% | 80% | Add campaign management, segmentation, templates, analytics |
| Performance | ~50% | 80% | Real optimization services, query analysis, caching recommendations |
| Webhook | ~70% | 95% | Add HMAC signature verification, event filtering, retry policies |

### Phase 1 — Developer Portal Extension

| Task | Priority | Description |
|------|----------|-------------|
| Plugin Development Guide | HIGH | Tutorial for building plugins: hooks API, permissions, sandbox, manifest format, security scan rules |
| GraphQL Documentation | HIGH | Developer-facing docs for 35 GraphQL schemas with query examples |
| Agent/MCP Development Guide | MEDIUM | How to build MCP tools, register agents, use AI orchestration |
| Event Streaming Docs | MEDIUM | Redis Streams publisher/consumer usage, event subscription patterns |
| Plugin SDK Package | LOW | Composer package with base classes, interfaces, and testing helpers |

### Phase 2 — Plugin Marketplace UI

| Task | Priority | Description |
|------|----------|-------------|
| Public Marketplace Page | HIGH | Customer-facing browse, search, filter, install plugins |
| Plugin Detail Page | HIGH | Description, screenshots, reviews, ratings, install button |
| Plugin Review System | MEDIUM | User reviews and ratings (PluginReview model exists) |
| Example Plugins | MEDIUM | 3-5 sample plugins demonstrating hooks, permissions, sandbox |
| Plugin Versioning UI | LOW | Update notifications, changelog, rollback |

### Phase 3 — Domain Completeness

| Task | Priority | Description |
|------|----------|-------------|
| Webhook HMAC Signing | HIGH | Signature verification for webhook deliveries (security gap) |
| Newsletter Campaigns | MEDIUM | Campaign builder, segmentation, scheduling, analytics |
| Activity Timeline | MEDIUM | User activity feeds, event-driven activity tracking |
| Performance Optimization | LOW | Real query analysis, N+1 detection, caching recommendations |
| Contact Ticketing | LOW | Auto-responders, assignment, status tracking |

### Phase 4 — Sub-Product Detail Pages

| Task | Priority | Description |
|------|----------|-------------|
| Exchange Detail Page | MEDIUM | Individual product page with features, pricing, API docs |
| Lending Detail Page | MEDIUM | Loan products, rates, application flow |
| Stablecoins Detail Page | MEDIUM | Supported stablecoins, reserve transparency, minting |
| Treasury Detail Page | MEDIUM | Portfolio tools, rebalancing strategies, reporting |

### Success Criteria — ALL COMPLETE (19/19)

- [x] Plugin development guide published in developer portal (PR #755)
- [x] GraphQL documentation page live (PR #756)
- [x] Event Streaming & MCP/Agent docs live (PR #757)
- [x] Plugin SDK documentation with complete reference (PR #775)
- [x] Public plugin marketplace UI functional (PR #770)
- [x] Plugin detail page with reviews, versioning, install commands (PR #770, #775)
- [x] Plugin review system — security reviews displayed on detail page (PR #775)
- [x] 2 example plugins: payment-analytics, compliance-notifier (PR #775)
- [x] Plugin versioning UI — version info + last updated on detail page (PR #775)
- [x] Webhook HMAC signing + encrypted secrets at rest (PR #760)
- [x] Newsletter campaigns — Campaign model + CampaignService with full lifecycle (PR #773, #774)
- [x] Activity timeline — ActivityService with logging, user feeds, stats, purge (PR #773)
- [x] Performance optimization — PerformanceReportService with KPIs, alerts, history (PR #773, #774)
- [x] Contact ticketing — ContactTicketService with workflow, auto-responder, stats (PR #773, #774)
- [x] Sub-product detail pages — Exchange, Lending, Stablecoins, Treasury (existed, rebranded PR #772)
- [x] All developer portal content rebranded to Zelta (PRs #748-#750, #759, #772)
- [x] Complete brand cleanup — 71 blade templates, 353 replacements (PR #772)
- [x] Security review + hardening pass (PRs #759, #760, #770, #774)
- [x] Token contract Alchemy webhook — scalable to unlimited users (PR #758)

### Delivered (March 17, 2026)

**Phase 1 — Developer Portal (5/5 COMPLETE):**
- Plugin Development Guide: 17 hooks, 12 permissions, lifecycle, manifest reference (PR #755)
- GraphQL API Docs: 35 domain schemas, subscriptions, rate limits (PR #756)
- Event Streaming Docs: 15 Redis Streams, publisher/consumer API (PR #757)
- MCP/AI Agent Docs: 16 banking tools, custom tool tutorial (PR #757)
- Plugin SDK: plugins/README.md with complete reference, interfaces, CLI commands (PR #775)

**Phase 2 — Plugin Marketplace UI (5/5 COMPLETE):**
- Public browse/search page with pagination, vendor + status filters (PR #770)
- Plugin detail page with permissions, dependencies, install commands (PR #770)
- Security reviews displayed on detail page with score/status (PR #775)
- 2 example plugins: payment-analytics (hooks), compliance-notifier (external API) (PR #775)
- Version info + last updated on detail page (PR #775)
- Security hardened: LIKE injection, route constraints, URL validation, production fallbacks (PR #770)

**Phase 3 — Domain Completeness (5/5 COMPLETE):**
- Webhook: encrypted secrets, HMAC signing, branded User-Agent (PR #760)
- Newsletter: Campaign model with draft/scheduled/sent lifecycle, CampaignService with lockForUpdate, cursor-based sending (PR #773, #774)
- Activity: ActivityService with logging, user timelines, system feed, stats, purge (PR #773)
- Contact: ContactTicketService with assign/respond/close workflow, status guards, auto-responder (PR #773, #774)
- Performance: PerformanceReportService with DB-level KPIs, threshold alerts, cached dashboard (PR #773, #774)

**Phase 4 — Sub-Product Detail Pages (4/4 COMPLETE):**
- Exchange, Lending, Stablecoins, Treasury detail pages (existed, rebranded in PR #772)

### Also Delivered (Not in Original Roadmap)
- RPC caching — 90% reduction in Alchemy calls (PR #752)
- WebSocket balance events — replaces 60s mobile polling (PR #753)
- Token contract Alchemy webhook — 6 contracts, scales to unlimited users (PR #758)
- Flaky CI test fix — UserOperationSigningServiceTest parallel isolation (PR #773)
- Complete Zelta rebrand — 71 blade templates, 353 replacements (PR #772)

### PRs: #746-#777 (23 PRs)

---

## v6.1.0 — Feature Completeness & Production Readiness (COMPLETED)

**Completed**: March 27, 2026 (delivered across v6.2.0–v6.12.0)
**Theme**: Every advertised feature at 100% — no gaps between marketing and implementation

### Comprehensive Feature Audit (March 17, 2026)

Professional architect audit of ALL 43 domains against what the features pages advertise.
15 domains at 75%+, 7 domains at 50-70%, 1 feature advertised but not implemented.

### Phase 1 — Security: Post-Quantum Cryptography (CRITICAL)

Features page advertises "quantum-resistant encryption" under Bank-Grade Security,
but zero quantum-related code exists. This is a credibility gap that must be fixed first.

| Task | Priority | Description |
|------|----------|-------------|
| Post-Quantum Key Encapsulation | CRITICAL | Implement ML-KEM (Kyber) for key exchange using phpseclib/libsodium |
| Post-Quantum Digital Signatures | CRITICAL | Implement ML-DSA (Dilithium) for digital signatures |
| Hybrid Encryption Service | CRITICAL | Classical + post-quantum hybrid mode for backwards compatibility |
| Quantum-Safe Key Rotation | HIGH | Key rotation service that upgrades existing keys to PQ-safe |
| Update Security Feature Page | HIGH | Accurate documentation of what PQ algorithms are implemented |

### Phase 2 — Card Issuance: Rain Integration (HIGH)

Card Issuance domain is thin (55%) — no persistent models, only demo adapter.
Rain is a modern card issuing platform for crypto/fintech companies.

| Task | Priority | Description |
|------|----------|-------------|
| Rain Card Issuer Adapter | HIGH | Implement CardIssuerInterface for Rain API (create, freeze, fund, spend limits) |
| Card Model + Migration | HIGH | Persistent Card model with status, last4, network, funding source |
| Cardholder Model | HIGH | KYC-linked cardholder with shipping address, verification status |
| Transaction Sync | MEDIUM | Sync card transactions from Rain webhooks |
| Card Management API | MEDIUM | REST + GraphQL endpoints for card lifecycle |

### Phase 3 — Banking Integration Completion (60% → 90%)

| Task | Priority | Description |
|------|----------|-------------|
| Complete syncBankAccounts | HIGH | Real implementation instead of empty collection stub |
| Open Banking Adapter | HIGH | PSD2/Open Banking API connector for EU bank data |
| Bank Transfer Service | MEDIUM | Complete inter-bank transfer workflow with status tracking |
| Account Verification | MEDIUM | Micro-deposit or instant verification flow |

### Phase 4 — Multi-Tenancy Hardening (50% → 90%)

| Task | Priority | Description |
|------|----------|-------------|
| Tenant Provisioning Service | HIGH | Create/configure/migrate tenants programmatically |
| Tenant Middleware | HIGH | Auto-resolve tenant from subdomain/header/token |
| Tenant Data Isolation Tests | MEDIUM | Verify no cross-tenant data leakage |
| Tenant Billing/Usage | LOW | Usage metering per tenant |

### Phase 5 — Event Streaming Hardening (50% → 85%)

| Task | Priority | Description |
|------|----------|-------------|
| Dead Letter Queue | HIGH | Failed messages routed to DLQ for manual review |
| Backpressure Handling | MEDIUM | Slow consumer detection + pause/resume |
| Stream Health Dashboard | MEDIUM | Lag metrics, consumer health, throughput charts |
| Message Schema Registry | LOW | Schema validation for published events |

### Phase 6 — Cross-Chain & DeFi Production Adapters (65-70% → 85%)

| Task | Priority | Description |
|------|----------|-------------|
| Wormhole Production Adapter | MEDIUM | Real Wormhole SDK integration (VAA submission) |
| Circle CCTP Adapter | MEDIUM | Real CCTP integration for USDC bridging |
| Uniswap V3 Quoter | MEDIUM | Real on-chain quote via Quoter contract |
| Aave V3 Position Reader | LOW | Read user positions from Aave on-chain |

### Phase 7 — Privacy ZK Production Prover (70% → 85%)

| Task | Priority | Description |
|------|----------|-------------|
| SnarkJS Integration | MEDIUM | Production ZK prover using SnarkJS/Circom |
| Trusted Setup Ceremony | LOW | SRS manifest for production circuit params |
| Proof Verification On-Chain | LOW | Solidity verifier contract integration |

### Success Criteria

- [x] Post-quantum encryption implemented and tested (ML-KEM + ML-DSA)
- [x] Rain card issuing adapter with persistent models
- [x] Banking syncBankAccounts fully implemented
- [x] Tenant provisioning service with middleware
- [x] Dead letter queue for event streaming
- [x] Every feature page claim backed by real code
- [x] All domains at 80%+ completeness

### v6.1.1 — Deferred Items (COMPLETED)

| Task | Status | Description |
|------|--------|-------------|
| Card Transaction Sync | DONE | CardTransactionSyncService with webhook processing (created/settled/declined/reversed) + polling |
| Card Management API | DONE | REST: CardholderController (list/create/show), GraphQL: full card lifecycle mutations + cardholder queries |
| Bank Transfer Service | DONE | BankTransferService with state machine (initiated→pending→processing→completed/failed), status tracking, cancellation |

---

### v6.2.0 — Visa CLI Integration (COMPLETED)

**Release Date**: March 21, 2026
**Theme**: Programmatic Visa Card Payments for AI Agents

| Component | Files | Description |
|-----------|-------|-------------|
| Domain Foundation | 32 | Contracts, services, models, migrations, events, exceptions, enums, data objects |
| MCP Tools | 2 | `visacli.payment` + `visacli.cards` for AI agent workflows |
| Payment Gateway | 2 | Invoice collection endpoint + webhook handler with HMAC verification |
| Card Enrollment | 1 | Event-driven sync to CardIssuance domain |
| Artisan Commands | 3 | `visa:status`, `visa:enroll`, `visa:pay` |
| Test Suite | 9 | 52 tests (unit, feature, integration) — all passing |
| Documentation | 3 | Feature page, feature index card, markdown docs |

**Security hardening**: SSRF prevention, atomic spending limits with row-level locking, webhook replay protection, log redaction, production-enforced signature verification.

---

### v6.3.0 — Virtuals Protocol Agent Integration (COMPLETED)

**Release Date**: March 23, 2026
**Theme**: AI Agent Commerce — Compliant Spending Bridge for Autonomous Agents

| Component | Files | Description |
|-----------|-------|-------------|
| VirtualsAgent Domain | 15 | Model, 6 services, 3 DTOs, 3 events, enum, config, migration |
| REST API | 2 | 7 endpoints for mobile agent management + aGDP reporting |
| ACP Bridge | 1 | Maps Virtuals ACP job requests to 5 Zelta service categories |
| Token Tracking | 1 | ERC-20 agent token balance reads via EthRpcClient |
| aGDP Reporting | 1 | Aggregate economic output metrics per agent/period |
| Pimlico Enforcement | 1 | Session key policy bridge for on-chain spending limits |
| TrustCert Extension | 1 modified | Agent-scoped certificate lookup (`?agent_id=` parameter) |
| Filament Dashboard | 5 | Full CRUD with suspend/activate actions, status badges |
| Feature Page | 1 | `/features/virtuals-protocol` with ACP catalog + code examples |
| Documentation | 1 | Architecture spec with 8-phase roadmap |

**Security hardening**: Atomic onboarding (DB transactions + lockForUpdate), input validation (agent ID pattern, employer existence, chain whitelist, spending limit caps), TrustCert delimiter injection prevention, URL format validation in ACP bridge, approved UserOp audit logging.

**Integration points**: ACP Service Provider (5 services), Butler discovery (via ACP), Agent Token tracking, aGDP reporting endpoint.

---

### v6.3.1 — Admin CLI & Registration Control (COMPLETED)

**Release Date**: March 23, 2026
**Theme**: Production Access Control

| Feature | Description |
|---------|-------------|
| `user:create --admin` | Create users via CLI (interactive or scripted) |
| `user:promote` / `user:demote` | Manage admin roles with audit logging |
| `user:admins` | List all admin users |
| Registration control | `REGISTRATION_ENABLED=false` disables public registration in production |
| Defense-in-depth | CreateNewUser action also checks flag (abort 403 if bypassed) |
| Last-admin protection | Cannot demote the last admin user |
| Audit logging | All role changes logged with user ID and email |
| Horizon memory | Master supervisor limit increased to 128MB (configurable) |

---

### v6.3.2 — Production Hardening & Landing Page (COMPLETED)

**Release Date**: March 23, 2026
**Theme**: Admin Operations, TrustCert Mobile Fixes, Landing Page Expansion

| Feature | Description |
|---------|-------------|
| User invitation system | Email invites via CLI + Filament admin dashboard, 72h expiry, single-use tokens |
| Admin module visibility | `ADMIN_MODULES` env var hides irrelevant Filament groups (25 → 11 for Zelta) |
| TrustCert API fixes | Documents pre-populated, requirements include documents array, null responses for no cert |
| TrustCert status normalization | Stored as mobile-native: pending/in_review (was draft/submitted) |
| Landing page expansion | 6 feature tabs (+AI Agents, +Identity), 4 sub-cards, stats strip, expanded marquee |
| CTA button fix | Replaced nonexistent btn-outline-light with btn-outline across all feature pages |
| Invitation security | Rate limiting, DB transactions, role whitelist, Filament authorization, token not in CLI output |
| Sitemap production fix | Respects SHOW_PROMO_PAGES, auth pages removed, generated files untracked from git |

---

### v6.3.3 — Mobile API Fixes + CI Green (COMPLETED)

**Release Date**: March 23, 2026
**Theme**: TrustCert Mobile Compatibility, Avatar Upload, Referral Registration

| Feature | Description |
|---------|-------------|
| TrustCert test fixes | Updated all test assertions for new response format (pending/in_review, numeric levels, documents array, null for no cert) |
| Referral in registration | `POST /auth/register` accepts optional `referral_code`, tracks `referred_by`, self-referral prevention, audit logging |
| Avatar upload | `POST /api/v1/users/avatar` (multipart, 2MB, 4096px max), `DELETE /api/v1/users/avatar` |
| Avatar security | Image dimension validation (decompression bomb), 10/min upload rate limit |
| Document upload fix | Validation accepts `id_front,id_back,selfie,proof_of_address,source_of_funds` (was `identity,address,kyc,audit`) |
| Auth rate limit fix | `GET /auth/user` moved to 100/min query rate (was 5/min auth rate with 5-min lockout) |
| CI green | All 16 CI jobs passing: code quality, unit tests, feature tests, integration, security, performance, load |

---

### v6.4.0 — Machine Payments Protocol + AP2 Mandates + Solana (COMPLETED)

**Release Date**: March 23, 2026
**Theme**: Multi-Protocol Agent Payments, Google AP2, Solana Launch

| Component | Files | Description |
|-----------|-------|-------------|
| MachinePay Domain | 41 | Full MPP protocol: Stripe SPT, Tempo, Lightning, Card rails |
| MPP Middleware | 1 | `MppPaymentGateMiddleware` with `WWW-Authenticate: Payment` headers |
| MPP MCP Tools | 2 | `mpp.payment` + `mpp.discovery` with -32042 error code binding |
| MPP API | 3 | Protocol status, monetized resource CRUD, payment history |
| AP2 Mandates | 25 | Cart/Intent/Payment mandates, VDCs, MandateService lifecycle |
| AP2 MCP Tools | 2 | `agent_protocol.mandate` + `agent_protocol.vdc` |
| Multi-Protocol Bridge | 1 | X402 + MPP + AP2 protocol selection service |
| Solana x402 | 2 | `solana:mainnet` + `solana:devnet` in X402Network enum |
| Helius Webhook | 1 | Solana balance monitoring via Enhanced Transactions |
| Feature Page | 1 | `/features/machine-payments` with rail comparison |
| Legal Disclaimers | 3 | Rizon-style platform positioning (landing + footer + FAQ) |
| Documentation | 4 | MPP, AP2, Multi-Protocol, Mobile Handover docs |
| Tests | 6 | 32 tests, 115 assertions — all passing |
| Dependabot | 7 | Merged: symfony/http-client, waterline, postcss, 4 Docker actions |

**Security hardening**: Transaction-level locking on settlement idempotency, HMAC key separation (derived keys, never reuse app key), mandate state machine with `lockForUpdate`, admin-only resource monetization, blocked sensitive path prefixes, production environment guards on demo adapters.

**Protocol comparison**:
- **x402** (Coinbase): USDC on EVM + Solana, custom headers, facilitator settlement
- **MPP** (Stripe + Tempo): Multi-rail fiat+crypto, standard HTTP auth, HMAC challenges
- **AP2** (Google): Cart/Intent/Payment mandates with VDCs, wraps x402+MPP as payment methods

---

## Version 6.4.1 — Helius Auto-Sync + Security Hardening (RELEASED)

**Release Date**: March 23, 2026

- Helius webhook auto-sync for Solana address monitoring
- HMAC key separation (derived domain-specific keys)
- Settlement idempotency with `lockForUpdate`
- CI fix: K8s workflow manual trigger only

---

## Version 6.4.2 — HyperSwitch Payment Orchestration (RELEASED)

**Release Date**: March 23, 2026

- HyperSwitch integration: 150+ payment connectors, smart routing, failover
- REST client for payments, refunds, customers, connectors
- HMAC-SHA512 webhook verification

---

## Version 6.4.3 — Swagger 403 Fix (RELEASED)

**Release Date**: March 23, 2026

- Moved `public/docs/` to `public/postman/` to resolve nginx 403 on Swagger
- HyperSwitch PHPStan compliance

---

## Version 6.5.0 — SMS Multi-Rail Payments + Mobile Launch (RELEASED)

**Release Date**: March 24, 2026
**Theme**: First Partner Integration + Mobile Readiness

### SMS Domain (VertexSMS)

| Component | Files | Description |
|-----------|-------|-------------|
| SMS Service | 6 | VertexSMS client, pricing, settlement, exchange rates |
| SMS Controller | 2 | Send (MPP-gated), rates, status, DLR webhook |
| x402 Rail Adapter | 1 | Bridges x402 facilitator as MPP payment rail |
| MCP Tool | 1 | `sms.send` for AI agent SMS discovery |
| AP2 Mandate | 1 | `SmsIntentMandate` template for enterprise campaigns |
| Migration | 1 | `sms_messages` table |
| Partner Spec | 1 | Multi-rail integration guide for VertexSMS |

### Mobile Launch Readiness

| Component | Description |
|-----------|-------------|
| Quest Triggers | 5 auto-completion listeners (login, payment, card, shield, transaction) |
| Device Attestation | Apple App Attest + Google Play Integrity verifiers |
| JIT Funding | Real balance via AccountQueryService (demo fallback) |
| Handover Doc | Complete mobile developer specification |

### Security Hardening
- DLR webhook: `DB::transaction` + `lockForUpdate` + forward-only state machine
- Rate limiting: SMS send (60/min), DLR webhook (200/min)
- E.164 phone validation, zero-rate pricing guard
- Stripe Connect with `transfer_data` for direct provider settlement

### PaymentRail Enum
- New `X402_USDC` case for USDC payments via x402 facilitator
- Total rails: Stripe SPT, Tempo, Lightning, Card, x402 USDC

---

## Version 6.6.0 — Zelta CLI, Payment SDK, Solana HSM, WebSocket Payments (RELEASED)

**Release Date**: March 26, 2026
**Theme**: Developer Tooling + Payment Protocol Expansion

- Zelta CLI v0.2.0 (25 commands across 8 resource groups)
- Zelta Payment SDK (packages/zelta-sdk — Packagist-ready)
- x402 .well-known/x402-configuration discovery endpoint
- Solana first-class rail (Ed25519 + HSM signer + verifier)
- Protocol subdomains (x402.api.* / mpp.api.*)
- WebSocket payment gate (paid channel subscriptions)
- CLI distribution pipeline (PHAR, npm, Homebrew, GH Releases)
- SMS demo seeding + mobile rewards auto-creation
- 4 security fixes from code review (payment verification guards, IDOR, key masking)
- PRs: #828–#849 (20 PRs merged)

---

## Version 6.7.0 — A2A Protocol + Developer Ecosystem (RELEASED)

**Release Date**: March 27, 2026
**Theme**: Agent-to-Agent Protocol Compliance + Developer Experience

### A2A Protocol Spec Compliance
- A2A Agent Card at /.well-known/agent.json (5 skills, streaming/push support)
- A2A Task lifecycle: send, get, cancel, list endpoints
- A2ATaskState enum with 6 states + validated transitions
- A2ATask model with UUID, state machine, query scopes

### Developer Ecosystem
- SDK Packagist publish workflow (triggered on sdk-v* tags)
- API Sandbox at /developers/sandbox (client-side API testing tool)
- 7 smoke tests for critical pages and API endpoints

### Cleanup
- Exchange LiquidityRetryPolicy TODO resolved
- VERSION_ROADMAP.md updated through v6.7.0

---

## Version 6.8.0 — Card Issuance Completion (RELEASED)

**Release Date**: March 27, 2026
**Theme**: Card Issuance Feature Completeness

### GraphQL API Completion
- 3 missing query resolvers: CardTransactionsQuery, CardholdersQuery, CardholderQuery
- 5 missing mutation resolvers: CreateCard, FreezeCard, UnfreezeCard, CancelCard, CreateCardholder
- All 12 GraphQL operations now fully functional (was 3/12)

### Spend Limit Enforcement
- SpendLimitEnforcementService with daily/monthly cache-backed tracking
- Integrated into JIT funding authorization flow
- DECLINED_LIMIT_EXCEEDED decision for over-limit transactions

### Testing
- 18 tests passing (12 GraphQL integration + 6 spend limit unit)
- PHPStan Level 8 compliant

---

## Version 6.9.0 — Banking Integration Hardening (RELEASED)

**Release Date**: March 27, 2026
**Theme**: Banking REST API + GraphQL Completion

### REST API Controllers
- BankingController: 8 endpoints (connect, disconnect, connections, accounts, sync, transfer, status, health)
- AccountVerificationController: 3 endpoints (micro-deposit initiate/confirm, instant verify)
- BankWebhookController: 2 endpoints (transfer-update, account-update) with HMAC signature verification

### GraphQL Completion
- Fixed AggregatedBalanceQuery (was returning hardcoded 0.0)
- Added BankTransfersQuery for transfer history
- Added CancelTransferMutation with state machine validation
- Registered banking.graphql in Lighthouse schema

### Testing
- 24 tests passing (14 REST controller + 10 GraphQL integration)
- PHPStan Level 8 compliant

---

## Version 6.10.0 — Multi-Tenancy Hardening (RELEASED)

**Release Date**: March 27, 2026
**Theme**: Tenant Security, Audit Logging, Plan Enforcement

### Audit Logging
- TenantAuditLog model + tenant_audit_logs migration
- TenantAuditService integrated into all TenantProvisioningService lifecycle methods
- Persistent audit trail for: create, suspend, reactivate, delete, plan change, config update

### Plan Enforcement
- EnforceTenantPlanLimits middleware — returns 429 when plan limits exceeded
- Integrates with TenantUsageMeteringService for real-time limit checking

### Soft-Delete & Lifecycle
- Tenant soft-delete with 14-day grace period (deletion_scheduled_at)
- restoreTenant() cancels scheduled deletion
- purgeTenant() enforces grace period before permanent deletion + DB drop

### Data Migration Hardening
- Table name whitelist in TenantDataMigrationService
- Rejects any non-whitelisted table names to prevent SQL injection vectors

---

## Version 6.11.0 — CrossChain/DeFi Production Adapters (RELEASED)

**Release Date**: March 27, 2026
**Theme**: Web3 Integration Layer + Production Protocol Adapters

### Infrastructure
- EthRpcClient — JSON-RPC client with circuit breaker, retry, multi-config URL resolution
- AbiEncoder — Solidity ABI encoding/decoding for address, uint256, uint16, uint32, bytes32, structs

### CrossChain Production Adapters
- Wormhole: TokenBridge.transferTokens() ABI encoding, Guardian VAA polling, destination receipt verification
- Circle CCTP: TokenMessenger.depositForBurn(), attestation polling, MessageTransmitter.receiveMessage()

### DeFi Production Adapters
- Uniswap V3: Quoter2.quoteExactInputSingle() struct encoding, SwapRouter02 with slippage protection
- Aave V3: Pool.supply/borrow/repay/withdraw() encoding, UiPoolDataProvider.getUserReservesData()

### Testing
- 52 new tests (AbiEncoder 25, EthRpcClient 13, Wormhole 6, Uniswap 8)

---

## Version 6.12.0 — Privacy ZK Production Prover (RELEASED)

**Release Date**: March 27, 2026
**Theme**: Zero-Knowledge Proving Infrastructure

### Circom Circuits (5)
- age_check.circom — age >= threshold without revealing birthdate
- residency_check.circom — region membership without revealing address
- kyc_tier_check.circom — KYC tier meets minimum without documents
- sanctions_check.circom — Merkle exclusion proof for sanctions clearance
- income_range_check.circom — income within range without exact amount

### Proving Infrastructure
- TrustedSetupService — Powers of Tau download, Groth16 setup, vkey/sol export
- CircuitCompilationService — Circom compilation wrapper, constraint counting
- ZkSetupCommand — `php artisan zk:setup --circuit=<name>` or `--all`
- SnarkjsProverService enhanced — .wasm validation, proving time metrics, constraint counts

### Solidity Verifiers (5)
- AgeCheckVerifier.sol, ResidencyCheckVerifier.sol, KycTierCheckVerifier.sol
- SanctionsCheckVerifier.sol, IncomeRangeCheckVerifier.sol

### Testing
- 33 new tests (TrustedSetup 13, CircuitCompilation 11, Roundtrip 9)
- PHPStan Level 8 compliant

---

## Version 7.0.0 — Production Release (RELEASED)

**Release Date**: March 28, 2026
**Theme**: Production-Grade Platform — Code Quality, SDK Stability, Deployment Readiness

### Web3 Infrastructure Consolidation
- Deprecated legacy Relayer EthRpcClient in favor of Infrastructure/Web3 canonical implementation
- Constructor injection for all 4 CrossChain/DeFi adapters (15 inline `new` sites eliminated)
- AbiEncoder and EthRpcClient now testable and mockable via DI

### Zelta SDK v1.0.0
- Version bump to 1.0.0 — stable API with typed DTOs and handler contracts
- PSR-4 autoload verified, PHPStan Level 8 clean

### Test Coverage Expansion
- 23 new tests: BankWebhookController (16 HMAC + payload tests), ZkSetupCommand (7 artisan tests)
- Total test suite stable across all domains

### Production Readiness Fixes
- Removed Marqeta sandbox URL from production env examples (was defaulting to sandbox API)
- Fixed `env()` calls in ProductionMerkleTreeService (would return null after config:cache)
- Helm Chart bumped to appVersion 7.0.0
- Added production guards to 5 demo services (Payment, Exchange, Lending, Stablecoin)

---

## Version 7.1.0 — Production Hardening (RELEASED)

**Release Date**: March 29, 2026
**Theme**: Observability, Mobile Compatibility, Security Readiness, Partner Onboarding

### Observability
- MetricsService with increment/gauge/timing + Prometheus exposition endpoint
- Health check endpoint (`/api/health`) with DB + cache + app checks
- JIT funding instrumented: latency, approval/decline counters
- Circuit breaker trip counter with chain tags
- Grafana dashboard: 8 panels across JIT Funding, API, Web3/Privacy

### Mobile App Compatibility
- API compatibility audit: 97/103 endpoints compatible
- Fixed 6 route mismatches: notifications path/method, relayer method, auth prefix, device routes
- All route aliases added without breaking existing endpoints

### Smoke Test Suite
- 36 E2E smoke tests across 5 files: health, auth, API, features, payments
- Lightweight SmokeTestCase (no DB migrations)
- Payment protocol discovery validation

### Partner Onboarding
- Partner Integration Guide with SDK quickstart, protocol comparison, webhook setup
- 5-phase onboarding checklist (14-day timeline)

### Security Audit Preparation
- Pentest scope document: full attack surface inventory (~1,360 routes, 40 GraphQL schemas)
- STRIDE threat model for 5 critical flows
- 15 prioritized findings (2 Critical, 4 High)
- Automated scan: zero CVEs, no leaked secrets, no dd()/dump()

---

## Version 7.1.1 — Security Hotfix (RELEASED)

**Release Date**: March 29, 2026
**Theme**: Critical Security Fixes from Threat Model

### JIT Funding TOCTOU Race Condition (Critical)
- Wrapped balance check + hold creation in `DB::transaction()` with `lockForUpdate()`
- Prevents double-spending via concurrent authorization requests
- Demo mode bypasses locking (no real account rows)

### Webhook SSRF Prevention (Critical)
- `UrlValidator::validateExternalUrl()` blocks private IPs, cloud metadata, loopback
- Enforces HTTPS in production
- Integrated into WebhookController (store/update) and AgentWebhookService
- 14 unit tests for all rejection/acceptance cases

---

## Version 7.2.0 — Standards & Compliance Foundation (RELEASED)

**Release Date**: March 30, 2026
**Theme**: Close traditional banking infrastructure gaps — ISO standards and Open Banking compliance

Based on competitive analysis of 19 worldwide open-source core banking platforms (Apache Fineract, Moov, Open Bank Project, Hyperswitch, Rafiki, Mojaloop, Galoy, and commercial platforms). FinAegis leads in Web3/DeFi/ZK/PQC; these gaps are in traditional banking infrastructure.

### ISO 20022 Message Engine (New Domain)
- 8 message type DTOs: Pain001, Pain008, Pacs008, Pacs002, Pacs003, Pacs004, Camt053, Camt054
- MessageParser — XML-to-DTO with namespace-aware parsing
- MessageGenerator — DTO-to-XML generation
- MessageValidator — 5-step validation (well-formed XML, namespace, enabled family, size, required fields)
- MessageRegistry — type-to-class mapping with namespace detection
- REST API: `/v1/iso20022/{validate,parse,generate,supported-types}`
- GraphQL: `iso20022Validate` mutation, `iso20022SupportedTypes` query
- UETR (Unique End-to-End Transaction Reference) for cross-border tracking

### Open Banking PSD2 Compliance (New Domain)
- PSD2 consent lifecycle: create → authorize → use → expire/revoke
- AISP (Account Information Service Provider) — consent-gated account/balance/transaction access
- PISP (Payment Initiation Service Provider) — consent-gated payment initiation
- TPP Registration Service — Third-Party Provider management with certificate validation
- Berlin Group NextGenPSD2 format adapter
- UK Open Banking format adapter
- ValidateTppCertificate middleware — eIDAS/QWAC certificate validation
- EnforceConsent middleware — consent-based access control
- REST API: 11 endpoints under `/v1/open-banking/`
- GraphQL: 2 queries + 3 mutations for consent management
- 3 models (Consent, TppRegistration, ConsentAccessLog) with migrations

### ISO 8583 Card Network Processor (New Domain)
- MessageCodec — encode/decode ISO 8583 bitmap-based messages
- FieldDefinitions — 25 standard fields (PAN, amount, STAN, terminal/merchant IDs, etc.)
- Bitmap — primary (64-bit) and secondary (128-bit) with hex encode/decode
- AuthorizationHandler — 0100→0110 auth request/response with PAN/amount validation
- ReversalHandler — 0400→0410 reversal processing
- SettlementHandler — 0500→0510 settlement batch processing
- REST API: `/v1/iso8583/{authorize,reverse,settle}`
- Integration point with CardIssuance domain for spend limit enforcement

### Statistics
- 3 new domains, ~51 files, 94 tests, 439 assertions
- PHPStan Level 8 — zero errors
- All features opt-in via config flags (ISO20022_ENABLED, OPEN_BANKING_ENABLED, ISO8583_ENABLED)

---

---

## Version 7.3.0 — Payment Rails (RELEASED)

**Release Date**: March 30, 2026
**Theme**: US payment rails, SEPA enhancement, intelligent routing, and Interledger interoperability

### US Payment Rails (New Domain: PaymentRails)
- **ACH** — NACHA file generation/parsing, originate credits/debits, same-day ACH, return processing (R01-R29)
- **Fedwire** — Real-time gross settlement with callback processing
- **RTP** — The Clearing House Real-Time Payments with Request-for-Payment
- **FedNow** — ISO 20022 native instant payments using Pacs008/Pacs002 from v7.2.0
- PaymentRailRouter — Intelligent dispatch to optimal rail
- 4 enums, 3 models, 3 migrations

### SEPA Enhancement (extend Banking domain)
- SEPA Direct Debit — mandate lifecycle, DD collection with ISO 20022 Pain008
- SEPA Credit Transfer — SCT and SCT Inst using Pain001/Pacs008
- SepaMandate model with 36-month expiry

### Intelligent Payment Routing
- ML-style weighted scoring (success rate 30%, latency 20%, cost 25%, availability 15%, urgency 10%)
- Atomic outcome recording, failover chains, operating hours, decision audit logging

### Interledger Protocol (New Domain)
- ILP Connector, Open Payments (GNAP), Cross-currency Quotes, Address Resolver
- REST API (7 endpoints) + GraphQL (3 queries + 3 mutations)

### Statistics
- 2 new domains + 1 extended, ~80 new files, 174 tests, 432 assertions

---

## Version 7.4.0 — Accounting & Infrastructure (RELEASED)

**Release Date**: March 30, 2026
**Theme**: Double-entry ledger engine with pluggable drivers for financial accounting

### Double-Entry Ledger Engine (New Domain)
- **LedgerService** — Post journal entries with double-entry invariant enforcement (bcmath precision), entry reversal
- **ChartOfAccountsService** — Account hierarchy (21 default accounts across 5 types), CRUD, seed command
- **EloquentDriver** — MySQL-backed default driver with balance, trial balance, and account history queries
- **TigerBeetleDriver** — Optional high-throughput driver via TigerBeetle HTTP API, graceful fallback when unreachable
- **PostingRuleEngine** — Auto-posting from domain events via configurable rules (event.amount expressions)
- **ReconciliationService** — Compare GL vs domain balances, flag discrepancies, resolution workflow
- **PostGlEntryListener** — Event listener for auto-posting GL entries from any domain event
- Config-driven driver swap: `LEDGER_DRIVER=eloquent|tigerbeetle`

### Models & Migrations
- LedgerAccount (code, name, type, parent hierarchy)
- JournalEntry + JournalLine (double-entry pairs with debit/credit amounts)
- PostingRule (event-triggered auto-posting rules)
- ReconciliationReport (GL vs domain balance comparison)

### API
- REST: 8 endpoints under `/v1/ledger/` (accounts, balances, entries, trial balance, reconciliation)
- GraphQL: 3 queries + 3 mutations with LedgerAccount, JournalEntry, TrialBalanceEntry types

### Statistics
- 1 new domain, ~35 new files, 60 tests, 175 assertions

---

## Version 7.5.0 — Market Expansion & Developer Experience (RELEASED)

**Release Date**: March 30, 2026
**Theme**: Full MFI suite for microfinance institutions + developer experience enhancements

### Full Microfinance Suite (New Domain)
- **Group Lending** — Joint liability groups, center hierarchy, meeting management with frequency-based scheduling
- **Loan Provisioning** — IFRS-compliant classification (standard/substandard/doubtful/loss), configurable rates and thresholds, batch reclassification
- **Share Accounts** — Cooperative shares with purchase/redeem, dividend calculation and distribution
- **Teller Operations** — Cashier vault management, cash-in/cash-out with balance guards, reconciliation
- **Field Officer** — Territory assignment, collection sheet generation, mobile sync
- **Savings Products** — Dormancy detection (configurable thresholds), simple and compound interest calculation
- 5 enums, 8 models, 8 migrations, 6 services
- REST API (14 endpoints), GraphQL (3 queries + 5 mutations), 3 artisan commands

### Developer Experience (Option A+)
- **Sandbox Provisioning** — Isolated sandbox environments with 3 seed profiles (basic/full/payments)
- **Sandbox Reset** — Clean state reset with re-seeding
- **Webhook Testing** — Test payload generation for 5 event types, webhook replay with HMAC signatures
- **Webhook Delivery Routes** — Event listing, test payload, delivery log endpoints
- **API Key Management** — CLI commands: `partner:api-key {create,rotate,revoke,list}`
- **Sandbox CLI** — `partner:sandbox:{create,reset}` with profile selection

### Mobile Compatibility Fixes
- GET /v1/wallet/balances: added `balance_formatted` and `change_24h` fields
- GET /v1/commerce/payment-requests/{id}: added nested `merchant` object

### Statistics
- 1 new domain + 2 extended, ~60 new files, 150+ tests

---

## Version 7.6.0 — Security Hardening (RELEASED)

**Release Date**: March 30, 2026
**Theme**: Complete threat model remediation + CI compatibility

### Threat Model — All 15 Findings Resolved
Findings #1-2 fixed in v7.1.1, findings #3-15 fixed in this release:

| # | Finding | Severity | Fix |
|---|---------|----------|-----|
| 3 | Bridge quote tampering | High | Server-side quote cache with 60s TTL, client sends quote_id only |
| 4 | ZK temp files persist on crash | High | try/finally cleanup with @unlink() |
| 5 | OB session-based state (fixation risk) | High | Cache-based nonce with 10-min TTL, single-use via Cache::pull() |
| 6 | ZK proof CPU exhaustion | High | Counting semaphore (3 slots default) via Cache::lock() |
| 7 | Tenant bypass via public static | Medium | Private property + controlled setter with audit logging |
| 8 | Coinbase/Paysera webhook replay | Medium | Delivery ID dedup cache (24h window) |
| 9 | No per-card JIT rate limiting | Medium | RateLimiter::attempt() per card token (10/min default) |
| 10 | Bridge sender unverified | Medium | BlockchainAddress ownership check before bridge execution |
| 11 | No HTTPS on outbound webhooks | Medium | HTTPS enforcement in production |
| 12 | Webhook payload leak | Medium | PayloadSanitizer strips sensitive field patterns |
| 13 | Circuit file integrity unchecked | Medium | SHA-256 manifest verification + zk:verify-circuits command |
| 14 | No bridge value limits | Low | Configurable per-tx/daily limits with atomic counters |
| 15 | Marqeta HMAC optional | Low | Production boot warning + health check integration |

### CI Compatibility
- Upgraded to PHPCS v4.0.1 (matches CI)
- Fixed all auto-fixable errors
- Zero PHPCS errors locally and in CI

*Document Version: 7.6.0*
*Created: January 11, 2026*

---

## Version 7.7.0 — Production Deployment Readiness (RELEASED)

**Release Date**: 2026-03-29
**Theme**: Helm chart alignment, card settings API, production environment review, benchmark tooling

### Helm Chart Update
- Bumped `appVersion` from `7.0.0` to `7.7.0`
- Bumped chart `version` from `1.0.0` to `1.7.0`
- Updated production image tag in `values-production.yaml` to `7.7.0`

### Card Settings API (PATCH /v1/cards/{cardId})
- New `update` method on `CardController` accepting `network_preference`, `nickname`, `notifications_enabled`
- Route registered in `app/Domain/CardIssuance/Routes/api.php` under auth:sanctum middleware
- OpenAPI/Swagger annotations included for API documentation generation

### Production Environment Review
- Added 7 missing env var groups to `.env.production.example` and `.env.zelta.example`:
  - `ISO20022_ENABLED`, `ISO20022_FAMILIES` (v7.2.0)
  - `OPEN_BANKING_ENABLED`, `OPEN_BANKING_STANDARD` (v7.2.0)
  - `ISO8583_ENABLED` (v7.2.0)
  - `ACH_ENABLED`, `FEDWIRE_ENABLED`, `RTP_ENABLED`, `FEDNOW_ENABLED` (v7.3.0)
  - `ILP_ENABLED` (v7.3.0)
  - `LEDGER_DRIVER` (v7.4.0)
  - `MFI_ENABLED` (v7.5.0)

### Benchmark Commands
- **`benchmark:ledger`** — GL posting throughput; posts N journal entries, reports entries/second. Usage: `php artisan benchmark:ledger --count=1000`
- **`benchmark:payment-rails`** — ISO 8583 codec round-trip throughput; encode+decode N messages, reports ops/second. Usage: `php artisan benchmark:payment-rails --count=5000`

### Production Readiness Scan
- Confirmed zero `env()` calls outside config files in all 7 new domains (ISO20022, OpenBanking, ISO8583, PaymentRails, Interledger, Ledger, Microfinance)
- All new domain configs default to `enabled => false` — safe for zero-config production deployments

### Security Audit Scope Update
- Updated `docs/SECURITY_AUDIT_SCOPE.md` from v7.0.0 to v7.7.0
- Added all 7 new domains (v7.2–v7.5) to in-scope section with attack surface notes
- Added 8 new pentest items (P1: ISO 20022 XXE, Open Banking consent, ISO 8583 bitmap overflow, ACH routing; P2: ledger invariant bypass, MFI vault imbalance, ILP relay trust, TigerBeetle fallback)
- Updated domain module count: 49 → 56 bounded contexts

### Statistics
- 2 new Artisan commands, 7 env var groups added, Helm chart updated, 1 new API route

*Document Version: 7.7.0*
*Created: January 11, 2026*
*Updated: 2026-03-29 (v7.7.0 Production Deployment Readiness)*
*Updated: March 30, 2026 (v7.6.0 Security Hardening)*

---

## Version 7.8.0 — Standards & Compliance (RELEASED)

**Release Date**: March 30, 2026
**Theme**: Consolidated release of v7.2-v7.7 feature development + website content

### Delivered Features
- 7 new domains: ISO 20022, Open Banking PSD2, ISO 8583, PaymentRails, Interledger, Ledger, Microfinance
- 3 extended domains: Banking (SEPA DD, intelligent routing), FinancialInstitution (sandbox), Webhook (payload sanitizer)
- Website: 8 new feature cards, 7 feature inner pages, professional copywriting pass
- Mobile: Device attestation wiring, recovery shard improvements
- npm audit clean, Railgun bridge patched
- PRs #863-#872

### Statistics
- 10 domains added/extended, 8 feature pages, 7 feature cards

---

## Version 7.8.1 — Website Polish (RELEASED)

**Release Date**: March 31, 2026
**Theme**: Public-facing website cleanup and consistency pass

### Delivered Features
- GCU page migrated to layouts.public with unified brand design
- Platform page: removed duplicate module cards, linked to features
- Public changelog page added at /changelog with v7.0-v7.8 timeline
- /features/gcu 301 redirects to /gcu
- Sitemap updated, footer updated

---

## Version 7.8.2 — Backend Fixes & Developer Portal (RELEASED)

**Release Date**: April 1, 2026
**Theme**: Infrastructure fixes and developer portal improvements

### Delivered Features
- API registration no longer blocked by Fortify admin gate (mobile users can always register)
- CRON expression fix: fraud.batch.schedule 'hourly' → '0 * * * *'
- Log rotation: stack channel defaults to daily (14-day retention)
- Developer portal: honest SDK install commands, standardized naming, OpenAPI link
- Rate limits consolidated, Hello World in quick start
- Marqeta HMAC downgraded to debug (sandbox-only)
- CORS: X-Tenant-ID header allowed
- Migration FK fix: consents.tpp_id type mismatch

---

## Version 7.8.3 — GoPlus/OFAC Address Screening (RELEASED)

**Release Date**: April 2, 2026
**Theme**: Compliance address screening

### Delivered Features
- GoPlus Security API integration for on-chain address risk scoring
- OFAC SDN list screening for Solana and EVM addresses
- Address screening middleware for wallet operations

---

## Version 7.8.4 — Solana Address Generation & Pre-Release Cleanup (RELEASED)

**Release Date**: April 3, 2026
**Theme**: Solana ed25519 address generation and pre-release cleanup

### Delivered Features
- Solana ed25519 address generation for wallet domain
- Pre-release cleanup and stabilization

---

## Version 7.9.0 — Solana Balances, Helius Webhooks & SEO Overhaul (RELEASED)

**Release Date**: April 4, 2026
**Theme**: Solana wallet integration, push notifications, and platform polish

### Delivered Features
- Solana wallet balances wired to `/wallet/balances` endpoint
- Helius webhook integration for real-time Solana transaction processing
- Alchemy Solana RPC migration
- FCM push notifications for transaction events
- 181 PHPStan fixes across the codebase
- SEO overhaul across all public-facing pages
- HeliusTransactionProcessor extraction for cleaner webhook handling
- Duplicate processing prevention per address in Helius webhook
- Helius API key restored as query param (their API requires it)

---

## Version 7.10.0 — Webhook Architecture Refactor + Mobile Backend Handover ✅ COMPLETED

**Release Date**: April 7, 2026
**PR**: #902
**Theme**: Webhook infrastructure hardening, paid KYC, Stripe Bridge ramp, and mobile backend handover

### Delivered Features
- Webhook Infrastructure: Per-user DB-stored webhooks (AlchemyWebhookManager), encrypted signing keys, 100K address sharding, SmartAccountObserver auto-registration
- Webhook Hardening: Async queue processing (ProcessAlchemyWebhookJob, ProcessHeliusWebhookJob), unique (tx_hash, chain) constraint, Cache-based dedup, spam filter, reorg detection
- Card Waitlist: POST join (race-safe with lockForUpdate) + GET status endpoints
- Paid KYC Verification: 3 payment methods (wallet deduction, Stripe card, IAP), VerificationPayment audit table, StripeKycWebhookController
- RequireKycVerification Middleware: Blocks Level 0 users from financial endpoints
- Stripe Bridge Ramp: Replaces Onramper — StripeBridgeService, generic RampWebhookController (provider-agnostic), async webhook processing
- Security: bcmath for all fiat amounts, encrypted stripe_client_secret, webhook replay protection, IAP production gate
- Pre-existing fixes: LedgerDriverInterface binding, CrossChain test auth, PimlicoBundler enum count

### Stats
- 66 files changed, +4,739/-1,441 lines, 5 migrations, 2 queue jobs, 4 controllers, 1 middleware

---

## Version 7.10.1 — Stripe Bridge Ramp Hardening (RELEASED)

**Release Date**: April 13, 2026
**Theme**: Production-grade Stripe Crypto Onramp integration and platform-generic ramp abstraction

### Delivered Features
- Working Stripe Crypto Onramp signature verification (HMAC-SHA256 with `t=<ts>,v1=<hmac>` parsing)
- Race-safe webhook processing with `DB::transaction()` + `lockForUpdate()` + terminal-state idempotency
- Platform-generic `RampProviderInterface` with `normalizeWebhookPayload()` and signature validation
- Lazy `RampProviderRegistry` with factory closures (no fake credentials needed for inactive providers)
- Parameterized provider-contract test suite + non-custody regression test
- Removed legacy `StripeBridgeWebhookController` (single `/api/v1/ramp/webhook/{provider}` entry point)

---

## Version 7.10.2 — Deployment Pipeline Fix (RELEASED)

**Release Date**: April 13, 2026
**Theme**: First deployable release since v7.10.0

### Delivered Features
- Deploy workflow OOM fix: unit tests now run in batched PHP processes via `bin/pest-batch`
- `BackpressureHandlerTest` isolation: array cache driver in pre-deployment validation
- `.env.example` / `.env.zelta.example` dotenv parse errors fixed (unquoted whitespace values)
- No runtime code changes — CI infrastructure only

---

## Version 7.10.3 — Onboarding Welcome Modal Fix (RELEASED)

**Release Date**: April 14, 2026
**Theme**: Unblock new-user registration by removing broken tour stub

### Delivered Features
- Removed broken `startTour()` JS stub that threw `TypeError` on every new registration
- Stripped dead "Take Tour" header button and `startOnboarding()` `.catch` fallback
- Blade-only fix — no backend, migration, or config changes

---

## Version 7.10.4 — Frontend Security Patch (RELEASED)

**Release Date**: April 14, 2026
**Theme**: Close critical and high npm audit findings in devDependencies

### Delivered Features
- Bumped `axios` ^1.13→^1.15 closing 5 critical SSRF advisories (GHSA-3p68-rc4w-qgx5)
- Bumped `vite` ^6.4.1→^6.4.2 closing 1 high path traversal advisory
- Both packages are dev/build-time only — no runtime code changes
- Audit: Critical 5→0, High 1→0

---

## Version 7.10.5 — npm Dependency Sweep (RELEASED)

**Release Date**: April 14, 2026
**Theme**: Semver-safe npm update and lockfile dedup

### Delivered Features
- `npm update` within existing `package.json` ranges, lockfile shrank ~500 lines
- Notable: `autoprefixer` 10.4→10.5, `postcss` 8.5.8→8.5.9, `@ledgerhq/hw-transport-webusb` 6.32→6.33
- Cleared remaining moderate npm audit advisory (`follow-redirects` transitive dedup)
- Audit: Moderate 1→0

---

## Version 7.10.6 — Composer Dependency Sweep (RELEASED)

**Release Date**: April 14, 2026
**Theme**: 230 semver-safe Composer package upgrades

### Delivered Features
- 230 packages upgraded within existing `composer.json` ranges (zero range edits)
- Notable: `laravel/framework` 12.55→12.56, `phpstan` 2.1.42→2.1.47, `php-cs-fixer` 3.94→3.95, `aws/aws-sdk-php` 3.373→3.379
- 6 PHPStan errors fixed (surfaced by Larastan 3.9.5 rule tightening)
- php-cs-fixer 3.95.1 repo-wide reformat (65 files, whitespace-only)
- Zero security advisories

---

## Version 7.10.7 — Safe-Major Composer Trio (RELEASED)

**Release Date**: April 15, 2026
**Theme**: Three deferred major-version Composer upgrades judged safe to take

### Delivered Features
- `laravel/tinker` v2→v3 (dev REPL, requires PHP 8.4 — already there)
- `resend/resend-php` v0→v1 (stable release, bounded to Resend mail adapter)
- `darkaonline/l5-swagger` v10→v11 (pulls in swagger-php v5, dev tooling)
- Fixed pre-existing OpenAPI annotation misattachment in `X402StatusController` (surfaced by swagger-php v5 strictness)
- Zero security advisories, zero runtime behaviour changes

*Document Version: 7.10.7*
*Updated: April 17, 2026 (v7.10.7 Safe-Major Composer Trio)*

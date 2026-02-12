# Version Roadmap Strategic Decisions

> **Purpose**: Record key decisions made during roadmap planning. Reference when making future architectural choices.

## Version Philosophy

### Semantic Versioning Strategy
- **MAJOR (x.0.0)**: Breaking changes, significant architecture shifts
- **MINOR (x.y.0)**: New features, non-breaking enhancements  
- **PATCH (x.y.z)**: Bug fixes, security updates, documentation

### Release Cadence
- Minor releases: Every 8-12 weeks
- Patch releases: As needed (security within 24-48 hours)
- Major releases: Every 6-12 months
- LTS: Major versions get 2 years security support

---

## v1.2.0 Decisions (Feature Completion)

### Why Agent Bridges First?
1. Agent Protocol is core differentiator
2. Integration gaps block real-world usage
3. Three bridges complete the feature set:
   - Payment: Agents can transact
   - Compliance: Agents are regulated
   - MCP: Agents use AI tools

### Why Yield Optimization?
- Existing TODO in controller
- Treasury domain is 85% complete
- Completes a major feature set

### Why Observability Now?
- Production readiness requirement
- No dashboards currently exist
- Enables debugging of future issues

---

## v1.3.0 Decisions (Platform Modularity)

### Domain Decoupling Rationale
- Current: Tight coupling between domains
- Target: Interface-based contracts
- Benefit: Pick-and-choose installation

### Module System Design
- Each domain gets `module.json` manifest
- Dependencies declared explicitly
- Installation via artisan commands

### GCU Separation Strategy
- Move to `examples/gcu-basket/`
- Serves as reference implementation
- Shows how to build custom baskets
- Reduces core complexity

---

## v2.0.0 Decisions (Major Evolution)

### Multi-Tenancy Justification
- Enterprise customers need isolation
- Database-level tenant scoping
- Per-tenant configuration

### Hardware Wallet Priority
- Ledger + Trezor first (market leaders)
- Security requirement for institutional
- Multi-signature enables corporate use

### Kubernetes-Native Approach
- Helm charts for deployment
- HPA for auto-scaling
- Service mesh ready (Istio)

---

## Completed Versions (Summary)\n\n- **v2.9.0** (Feb 10, 2026): ML Anomaly Detection & BaaS (Statistical/Behavioral/Velocity/Geo anomaly detection, Partner SDKs, Billing, Widgets, Marketplace, 26 Partner API endpoints)\n- **v2.7.0** (Feb 8, 2026): Mobile Payment API & Enhanced Authentication (Payment Intents, Passkey Auth, P2P Transfer Helpers, TrustCert Export, Security Hardening)\n- **v2.6.0** (Feb 2, 2026): Privacy Layer & ERC-4337 (Merkle Trees, Smart Accounts, Delegated Proofs, UserOp Signing with Biometric JWT, Production-Ready Gas Station)\n- **v2.5.0**: Mobile App Launch (Expo/React Native)\n- **v2.4.0**: Privacy & Identity (Key Management, ZK-KYC, Commerce, TrustCert)\n- **v2.3.0**: AI Framework, RegTech Foundation, BaaS Configuration\n- **v2.2.0**: Mobile Backend (Device Management, Biometrics, Push Notifications)\n- **v2.1.0**: Security Hardening, Hardware Wallets, WebSocket, Kubernetes\n- **v2.0.0**: Multi-Tenancy with Team-Based Isolation\n\n### Next Planned\n- **v2.9.1** (Feb 10, 2026): Production Hardening (On-Chain SBT, snarkjs, AWS KMS, Azure Key Vault, Security Audit)
- **v2.10.0** (Feb 10, 2026): Mobile API Compatibility (~30 mobile-facing API endpoints)
- **v3.0.0** (Feb 10, 2026): Cross-Chain & DeFi (Bridge protocols: Wormhole/LayerZero/Axelar, DeFi: Uniswap/Aave/Curve/Lido, cross-chain swaps, multi-chain portfolio)

### Next Planned
- **v3.1.0** (Feb 11, 2026): Consolidation, Documentation & UI Completeness — Swagger annotations, 7 feature pages, 15 Filament admin resources, 4 user-facing views, developer portal update (8 phases, PRs #456-#465)
- **v3.2.0 — Production Readiness & Plugin Architecture (COMPLETED)
Status: Released (2026-02-11)
Phases: 6 phases across 6 PRs (#466-#471)
Key deliverables:
- 41 domain module manifests with enable/disable toggle
- ModuleRouteLoader extracting 1,646-line api.php into 24 per-domain route files
- Module REST API + Filament admin page + health widget
- k6 load test suite (smoke/load/stress), QueryPerformanceMiddleware, performance:report command
- GitHub community files (Dependabot, issue templates, PR template)
- SPDX license headers, 24 integration tests covering plugin system
v3.3.0 — Event Store Optimization & Observability (COMPLETED)
Status: Released (2026-02-12)
Phases: 6 phases across 6 PRs (#493-#498)
Key deliverables:
- EventStoreService centralizing event store operations for 21 domains
- event:stats, event:replay, event:rebuild, snapshot:cleanup commands with --dry-run
- EventStoreDashboard Filament page with 5 widgets (stats, throughput, aggregate health, system metrics, domain health)
- StructuredJsonFormatter, StructuredLoggingMiddleware, LogsWithDomainContext trait
- EventStoreHealthCheck with connectivity, projector lag, snapshot freshness, growth rate checks
- EventArchivalService with archive, compact, restore methods; archived_events table; event-store config
- 3 integration test suites covering all features

Future roadmap:
- v3.4.0 — API Maturity & DX (API versioning, rate limiting per tier, SDK auto-gen, OpenAPI 100%)
- v3.5.0 — Compliance Certification (SOC 2, PCI DSS, multi-region, GDPR tooling)
- v4.0.0 — Architecture Evolution (Laravel 13, Event Store v2, GraphQL, Plugin Marketplace)
## Architecture Principles (for decision-making)

1. **Interface First**: Extract contracts before implementations
2. **Event Sourcing Everywhere**: All state changes through events
3. **Demo Mode Parity**: Every feature works in demo
4. **Test Coverage**: Minimum 50%, 80%+ for financial logic
5. **PHPStan Level 8**: No regressions allowed
6. **Backward Compatibility**: Until major versions

---

## Key Files for Roadmap
- `docs/VERSION_ROADMAP.md` - Full roadmap document
- `docs/ARCHITECTURAL_ROADMAP.md` - Architecture vision
- `CHANGELOG.md` - Version history
- This memory - Decision rationale

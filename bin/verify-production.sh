#!/usr/bin/env bash
# =============================================================================
# Zelta Production Verification Script
# =============================================================================
# Verifies all services, credentials, and integrations are working.
# Run from the project root: ./bin/verify-production.sh
# =============================================================================

set -euo pipefail

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

PASS=0
FAIL=0
WARN=0

pass() { ((PASS++)); echo -e "  ${GREEN}✓${NC} $1"; }
fail() { ((FAIL++)); echo -e "  ${RED}✗${NC} $1"; }
warn() { ((WARN++)); echo -e "  ${YELLOW}!${NC} $1"; }
section() { echo -e "\n${CYAN}━━━ $1 ━━━${NC}"; }

APP_URL="${APP_URL:-https://zelta.app}"

# =============================================================================
section "1. Laravel Application"
# =============================================================================

# Health endpoint
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "${APP_URL}/up" 2>/dev/null || echo "000")
if [[ "$HTTP_CODE" == "200" ]]; then
    pass "Laravel health endpoint /up (HTTP $HTTP_CODE)"
else
    fail "Laravel health endpoint /up (HTTP $HTTP_CODE)"
fi

# Status page
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "${APP_URL}/status" 2>/dev/null || echo "000")
if [[ "$HTTP_CODE" == "200" ]]; then
    pass "Status page /status (HTTP $HTTP_CODE)"
else
    fail "Status page /status (HTTP $HTTP_CODE)"
fi

# API alive probe
ALIVE=$(curl -s "${APP_URL}/api/monitoring/alive" 2>/dev/null || echo '{}')
if echo "$ALIVE" | grep -q '"alive":true\|"status":"ok"'; then
    pass "API liveness probe /api/monitoring/alive"
else
    fail "API liveness probe /api/monitoring/alive"
fi

# Security headers
HEADERS=$(curl -sI "${APP_URL}/up" 2>/dev/null || echo "")
if echo "$HEADERS" | grep -qi "x-content-type-options: nosniff"; then
    pass "Security headers present (X-Content-Type-Options)"
else
    fail "Security headers missing"
fi
if echo "$HEADERS" | grep -qi "strict-transport-security"; then
    pass "HSTS header present"
else
    fail "HSTS header missing"
fi
if echo "$HEADERS" | grep -qi "content-security-policy"; then
    pass "CSP header present"
else
    fail "CSP header missing"
fi

# =============================================================================
section "2. Database (MariaDB)"
# =============================================================================

DB_CHECK=$(php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'OK'; } catch(Exception \$e) { echo 'FAIL: '.\$e->getMessage(); }" 2>/dev/null || echo "FAIL")
if [[ "$DB_CHECK" == *"OK"* ]]; then
    pass "MariaDB connection"
else
    fail "MariaDB connection: $DB_CHECK"
fi

# Check migrations are current
MIGRATE_STATUS=$(php artisan migrate:status 2>/dev/null | grep -c "Pending" || echo "0")
if [[ "$MIGRATE_STATUS" == "0" ]]; then
    pass "All migrations applied"
else
    warn "$MIGRATE_STATUS pending migrations"
fi

# =============================================================================
section "3. Redis"
# =============================================================================

REDIS_CHECK=$(php artisan tinker --execute="try { \Illuminate\Support\Facades\Redis::ping(); echo 'OK'; } catch(Exception \$e) { echo 'FAIL: '.\$e->getMessage(); }" 2>/dev/null || echo "FAIL")
if [[ "$REDIS_CHECK" == *"OK"* ]]; then
    pass "Redis connection"
else
    fail "Redis connection: $REDIS_CHECK"
fi

# Cache read/write
CACHE_CHECK=$(php artisan tinker --execute="try { cache()->put('__verify__', 'ok', 10); echo cache()->get('__verify__'); cache()->forget('__verify__'); } catch(Exception \$e) { echo 'FAIL'; }" 2>/dev/null || echo "FAIL")
if [[ "$CACHE_CHECK" == *"ok"* ]]; then
    pass "Cache read/write"
else
    fail "Cache read/write"
fi

# =============================================================================
section "4. RAILGUN Bridge"
# =============================================================================

BRIDGE_URL=$(php artisan tinker --execute="echo config('privacy.railgun.bridge_url');" 2>/dev/null || echo "")
BRIDGE_SECRET=$(php artisan tinker --execute="echo config('privacy.railgun.bridge_secret');" 2>/dev/null || echo "")

if [[ -n "$BRIDGE_URL" ]]; then
    # Health endpoint (public, no auth)
    BRIDGE_HEALTH=$(curl -s --connect-timeout 5 "${BRIDGE_URL}/health" 2>/dev/null || echo '{}')
    if echo "$BRIDGE_HEALTH" | grep -q '"engine_ready":true'; then
        pass "RAILGUN bridge health (engine ready)"
        # Show loaded networks
        NETWORKS=$(echo "$BRIDGE_HEALTH" | grep -o '"loaded_networks":\[[^]]*\]' || echo "")
        if [[ -n "$NETWORKS" ]]; then
            pass "RAILGUN networks: $NETWORKS"
        fi
    elif echo "$BRIDGE_HEALTH" | grep -q '"status":"initializing"'; then
        warn "RAILGUN bridge is still initializing"
    else
        fail "RAILGUN bridge not responding at $BRIDGE_URL"
    fi

    # Auth test (should return 401 without token)
    AUTH_TEST=$(curl -s -o /dev/null -w "%{http_code}" --connect-timeout 5 "${BRIDGE_URL}/wallet/create" -X POST 2>/dev/null || echo "000")
    if [[ "$AUTH_TEST" == "401" ]]; then
        pass "RAILGUN bridge auth enforced (401 without token)"
    elif [[ "$AUTH_TEST" == "000" ]]; then
        fail "RAILGUN bridge not reachable"
    else
        warn "RAILGUN bridge auth returned HTTP $AUTH_TEST (expected 401)"
    fi

    # Auth with correct token
    if [[ -n "$BRIDGE_SECRET" ]]; then
        AUTH_OK=$(curl -s -o /dev/null -w "%{http_code}" --connect-timeout 5 \
            -H "Authorization: Bearer ${BRIDGE_SECRET}" \
            -H "Content-Type: application/json" \
            -d '{}' \
            "${BRIDGE_URL}/wallet/create" -X POST 2>/dev/null || echo "000")
        if [[ "$AUTH_OK" == "422" || "$AUTH_OK" == "400" ]]; then
            pass "RAILGUN bridge auth accepts valid token (HTTP $AUTH_OK = validation error, auth passed)"
        elif [[ "$AUTH_OK" == "503" ]]; then
            warn "RAILGUN bridge auth OK but engine not ready (503)"
        else
            fail "RAILGUN bridge auth with valid token returned HTTP $AUTH_OK"
        fi
    fi
else
    fail "RAILGUN bridge URL not configured"
fi

# =============================================================================
section "5. Alchemy RPC"
# =============================================================================

ALCHEMY_KEY=$(php artisan tinker --execute="echo config('relayer.balance.alchemy_api_key') ?: env('ALCHEMY_API_KEY');" 2>/dev/null || echo "")
if [[ -n "$ALCHEMY_KEY" ]]; then
    # Test Polygon RPC
    POLYGON_BLOCK=$(curl -s --connect-timeout 5 -X POST \
        -H "Content-Type: application/json" \
        -d '{"jsonrpc":"2.0","method":"eth_blockNumber","params":[],"id":1}' \
        "https://polygon-mainnet.g.alchemy.com/v2/${ALCHEMY_KEY}" 2>/dev/null || echo '{}')
    if echo "$POLYGON_BLOCK" | grep -q '"result"'; then
        BLOCK_HEX=$(echo "$POLYGON_BLOCK" | grep -o '"result":"[^"]*"' | cut -d'"' -f4)
        pass "Alchemy Polygon RPC (latest block: $BLOCK_HEX)"
    else
        fail "Alchemy Polygon RPC not responding"
    fi

    # Test Ethereum RPC
    ETH_BLOCK=$(curl -s --connect-timeout 5 -X POST \
        -H "Content-Type: application/json" \
        -d '{"jsonrpc":"2.0","method":"eth_blockNumber","params":[],"id":1}' \
        "https://eth-mainnet.g.alchemy.com/v2/${ALCHEMY_KEY}" 2>/dev/null || echo '{}')
    if echo "$ETH_BLOCK" | grep -q '"result"'; then
        pass "Alchemy Ethereum RPC"
    else
        fail "Alchemy Ethereum RPC not responding"
    fi
else
    fail "Alchemy API key not configured"
fi

# =============================================================================
section "6. Pimlico (ERC-4337 Bundler)"
# =============================================================================

PIMLICO_KEY=$(php artisan tinker --execute="echo config('relayer.pimlico.api_key');" 2>/dev/null || echo "")
if [[ -n "$PIMLICO_KEY" ]]; then
    PIMLICO_RESP=$(curl -s --connect-timeout 5 -X POST \
        -H "Content-Type: application/json" \
        -d '{"jsonrpc":"2.0","method":"eth_chainId","params":[],"id":1}' \
        "https://api.pimlico.io/v2/137/rpc?apikey=${PIMLICO_KEY}" 2>/dev/null || echo '{}')
    if echo "$PIMLICO_RESP" | grep -q '"result"'; then
        pass "Pimlico bundler API (Polygon)"
    elif echo "$PIMLICO_RESP" | grep -q '"error"'; then
        ERROR=$(echo "$PIMLICO_RESP" | grep -o '"message":"[^"]*"' | head -1)
        fail "Pimlico bundler: $ERROR"
    else
        fail "Pimlico bundler not responding"
    fi
else
    fail "Pimlico API key not configured"
fi

# =============================================================================
section "7. CoinGecko (Exchange Rates)"
# =============================================================================

CG_KEY=$(php artisan tinker --execute="echo config('exchange.providers.coingecko.api_key');" 2>/dev/null || echo "")
CG_ENABLED=$(php artisan tinker --execute="echo config('exchange.providers.coingecko.enabled') ? 'true' : 'false';" 2>/dev/null || echo "false")
if [[ "$CG_ENABLED" == *"true"* && -n "$CG_KEY" ]]; then
    CG_RESP=$(curl -s --connect-timeout 5 \
        -H "x-cg-demo-api-key: ${CG_KEY}" \
        "https://api.coingecko.com/api/v3/ping" 2>/dev/null || echo '{}')
    if echo "$CG_RESP" | grep -q "gecko_says"; then
        pass "CoinGecko API (ping OK)"
    else
        fail "CoinGecko API not responding"
    fi

    # Test actual price fetch
    CG_PRICE=$(curl -s --connect-timeout 5 \
        -H "x-cg-demo-api-key: ${CG_KEY}" \
        "https://api.coingecko.com/api/v3/simple/price?ids=usd-coin&vs_currencies=eur" 2>/dev/null || echo '{}')
    if echo "$CG_PRICE" | grep -q "eur"; then
        pass "CoinGecko USDC/EUR price fetch"
    else
        warn "CoinGecko price fetch failed (rate limit?)"
    fi
else
    warn "CoinGecko not enabled or API key missing"
fi

# =============================================================================
section "8. Pusher (Broadcasting)"
# =============================================================================

PUSHER_KEY=$(php artisan tinker --execute="echo config('broadcasting.connections.pusher.key');" 2>/dev/null || echo "")
PUSHER_CLUSTER=$(php artisan tinker --execute="echo config('broadcasting.connections.pusher.options.cluster');" 2>/dev/null || echo "")
if [[ -n "$PUSHER_KEY" && -n "$PUSHER_CLUSTER" ]]; then
    PUSHER_RESP=$(curl -s --connect-timeout 5 \
        "https://sockjs-${PUSHER_CLUSTER}.pusher.com/pusher/info?app_key=${PUSHER_KEY}" 2>/dev/null || echo '{}')
    if echo "$PUSHER_RESP" | grep -q "websocket\|origins"; then
        pass "Pusher WebSocket endpoint reachable"
    else
        # Fallback check — just verify the API endpoint responds
        PUSHER_HTTP=$(curl -s -o /dev/null -w "%{http_code}" --connect-timeout 5 \
            "https://api-${PUSHER_CLUSTER}.pusher.com/apps/${PUSHER_KEY}" 2>/dev/null || echo "000")
        if [[ "$PUSHER_HTTP" != "000" ]]; then
            pass "Pusher API reachable (HTTP $PUSHER_HTTP)"
        else
            fail "Pusher not reachable"
        fi
    fi
else
    fail "Pusher not configured"
fi

# =============================================================================
section "9. Firebase (Push Notifications)"
# =============================================================================

FB_CREDS=$(php artisan tinker --execute="echo config('firebase.projects.app.credentials');" 2>/dev/null || echo "")
FB_PROJECT=$(php artisan tinker --execute="echo config('firebase.projects.app.project_id') ?: env('FIREBASE_PROJECT_ID');" 2>/dev/null || echo "")
if [[ -n "$FB_CREDS" ]]; then
    if [[ -f "storage/firebase-credentials.json" ]]; then
        pass "Firebase credentials file exists"
        # Validate JSON
        if python3 -m json.tool storage/firebase-credentials.json > /dev/null 2>&1; then
            pass "Firebase credentials file is valid JSON"
            # Check it has required fields
            if grep -q "project_id" storage/firebase-credentials.json; then
                pass "Firebase credentials contain project_id"
            else
                fail "Firebase credentials missing project_id field"
            fi
        else
            fail "Firebase credentials file is not valid JSON"
        fi
    else
        fail "Firebase credentials file not found at storage/firebase-credentials.json"
    fi
else
    warn "Firebase credentials path not configured"
fi

# =============================================================================
section "10. Stripe"
# =============================================================================

STRIPE_KEY=$(php artisan tinker --execute="echo config('services.stripe.secret') ?: config('cashier.secret');" 2>/dev/null || echo "")
if [[ -n "$STRIPE_KEY" && "$STRIPE_KEY" == sk_live_* ]]; then
    STRIPE_RESP=$(curl -s --connect-timeout 5 \
        -u "${STRIPE_KEY}:" \
        "https://api.stripe.com/v1/balance" 2>/dev/null || echo '{}')
    if echo "$STRIPE_RESP" | grep -q '"available"'; then
        pass "Stripe API (live mode, balance accessible)"
    elif echo "$STRIPE_RESP" | grep -q '"error"'; then
        ERROR=$(echo "$STRIPE_RESP" | grep -o '"message":"[^"]*"' | head -1)
        fail "Stripe API: $ERROR"
    else
        fail "Stripe API not responding"
    fi
elif [[ -n "$STRIPE_KEY" && "$STRIPE_KEY" == sk_test_* ]]; then
    warn "Stripe is in TEST mode"
else
    warn "Stripe secret key not configured"
fi

# =============================================================================
section "11. TrustCert Signing Keys"
# =============================================================================

TC_CA=$(php artisan tinker --execute="echo config('trustcert.ca.ca_signing_key') ? 'SET' : 'EMPTY';" 2>/dev/null || echo "EMPTY")
TC_CRED=$(php artisan tinker --execute="echo config('trustcert.signing.credential_signing_key') ? 'SET' : 'EMPTY';" 2>/dev/null || echo "EMPTY")
TC_PRES=$(php artisan tinker --execute="echo config('trustcert.signing.presentation_signing_key') ? 'SET' : 'EMPTY';" 2>/dev/null || echo "EMPTY")

[[ "$TC_CA" == *"SET"* ]] && pass "TrustCert CA signing key configured" || fail "TrustCert CA signing key missing"
[[ "$TC_CRED" == *"SET"* ]] && pass "TrustCert credential signing key configured" || fail "TrustCert credential signing key missing"
[[ "$TC_PRES" == *"SET"* ]] && pass "TrustCert presentation signing key configured" || fail "TrustCert presentation signing key missing"

# =============================================================================
section "12. Privacy / RAILGUN Config"
# =============================================================================

PP_ENABLED=$(php artisan tinker --execute="echo config('privacy.privacy_pools.enabled') ? 'true' : 'false';" 2>/dev/null || echo "false")
ZK_PROV=$(php artisan tinker --execute="echo config('privacy.zk.provider');" 2>/dev/null || echo "")
MK_PROV=$(php artisan tinker --execute="echo config('privacy.merkle.provider');" 2>/dev/null || echo "")

[[ "$PP_ENABLED" == *"true"* ]] && pass "Privacy pools enabled" || fail "Privacy pools disabled (PRIVACY_POOLS_ENABLED=false)"
[[ "$ZK_PROV" == *"railgun"* ]] && pass "ZK provider: railgun" || warn "ZK provider: $ZK_PROV (expected: railgun)"
[[ "$MK_PROV" == *"railgun"* ]] && pass "Merkle provider: railgun" || warn "Merkle provider: $MK_PROV (expected: railgun)"

# =============================================================================
section "13. X402 Protocol"
# =============================================================================

X402_ON=$(php artisan tinker --execute="echo config('x402.enabled') ? 'true' : 'false';" 2>/dev/null || echo "false")
X402_ADDR=$(php artisan tinker --execute="echo config('x402.pay_to_address');" 2>/dev/null || echo "")

[[ "$X402_ON" == *"true"* ]] && pass "X402 protocol enabled" || warn "X402 protocol disabled"
if [[ -n "$X402_ADDR" && "$X402_ADDR" == 0x* ]]; then
    pass "X402 pay-to address: ${X402_ADDR:0:10}..."
else
    warn "X402 pay-to address not set"
fi

# =============================================================================
section "14. HSM / Key Management"
# =============================================================================

HSM_ON=$(php artisan tinker --execute="echo config('keymanagement.hsm.enabled') ? 'true' : 'false';" 2>/dev/null || echo "false")
HSM_PROV=$(php artisan tinker --execute="echo config('keymanagement.hsm.provider');" 2>/dev/null || echo "")

[[ "$HSM_ON" == *"true"* ]] && pass "HSM enabled (provider: $HSM_PROV)" || warn "HSM disabled"

# =============================================================================
section "15. Queue & Supervisor"
# =============================================================================

# Check supervisor processes
if command -v supervisorctl &> /dev/null; then
    SUPERVISOR_STATUS=$(sudo supervisorctl status 2>/dev/null || echo "")
    if [[ -n "$SUPERVISOR_STATUS" ]]; then
        while IFS= read -r line; do
            PROC=$(echo "$line" | awk '{print $1}')
            STATE=$(echo "$line" | awk '{print $2}')
            if [[ "$STATE" == "RUNNING" ]]; then
                pass "Supervisor: $PROC is RUNNING"
            elif [[ "$STATE" == "STARTING" ]]; then
                warn "Supervisor: $PROC is STARTING"
            else
                fail "Supervisor: $PROC is $STATE"
            fi
        done <<< "$SUPERVISOR_STATUS"
    else
        warn "No supervisor processes found"
    fi
else
    warn "supervisorctl not available"
fi

# =============================================================================
section "16. Artisan Health Check"
# =============================================================================

ARTISAN_HEALTH=$(php artisan system:health-check 2>&1 || echo "COMMAND_FAILED")
if [[ "$ARTISAN_HEALTH" == *"COMMAND_FAILED"* ]]; then
    warn "system:health-check command not available"
else
    # Count pass/fail from artisan output
    ARTISAN_PASS=$(echo "$ARTISAN_HEALTH" | grep -ci "pass\|ok\|healthy\|✓\|✅" || echo "0")
    ARTISAN_FAIL=$(echo "$ARTISAN_HEALTH" | grep -ci "fail\|error\|unhealthy\|✗\|❌" || echo "0")
    if [[ "$ARTISAN_FAIL" -gt 0 ]]; then
        fail "Artisan health check: $ARTISAN_FAIL failures"
        echo "$ARTISAN_HEALTH" | grep -i "fail\|error\|unhealthy" | head -5 | sed 's/^/    /'
    else
        pass "Artisan health check passed"
    fi
fi

# =============================================================================
section "17. Pending Integrations (Info Only)"
# =============================================================================

# Ondato
ONDATO_ID=$(php artisan tinker --execute="echo config('services.ondato.application_id') ?: 'EMPTY';" 2>/dev/null || echo "EMPTY")
if [[ "$ONDATO_ID" != *"EMPTY"* && -n "$ONDATO_ID" ]]; then
    pass "Ondato KYC configured"
else
    warn "Ondato KYC not configured (registration in progress)"
fi

# Marqeta
MARQETA_URL=$(php artisan tinker --execute="echo config('cardissuance.marqeta.base_url');" 2>/dev/null || echo "")
if [[ "$MARQETA_URL" == *"sandbox"* ]]; then
    warn "Marqeta still on SANDBOX (registration in progress)"
elif [[ -n "$MARQETA_URL" ]]; then
    pass "Marqeta on production URL"
else
    warn "Marqeta not configured"
fi

# =============================================================================
# Summary
# =============================================================================
echo ""
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
TOTAL=$((PASS + FAIL + WARN))
echo -e "  ${GREEN}Passed:${NC}   $PASS"
echo -e "  ${RED}Failed:${NC}   $FAIL"
echo -e "  ${YELLOW}Warnings:${NC} $WARN"
echo -e "  Total:    $TOTAL"
echo -e "${CYAN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

if [[ "$FAIL" -gt 0 ]]; then
    echo -e "\n${RED}Some checks failed. Review above.${NC}"
    exit 1
else
    echo -e "\n${GREEN}All critical checks passed.${NC}"
    exit 0
fi

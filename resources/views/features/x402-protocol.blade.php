@extends('layouts.public')

@section('title', 'x402 Protocol - HTTP-Native Micropayments | FinAegis')

@section('seo')
    @include('partials.seo', [
        'title' => 'x402 Protocol - HTTP-Native Micropayments',
        'description' => 'Monetize APIs with HTTP 402 responses. USDC payments on Base with instant settlement, AI agent support, and spending limits.',
        'keywords' => 'x402, HTTP 402, micropayments, USDC, Base, API monetization, AI agent payments, spending limits',
    ])
@endsection

@push('styles')
<style>
    .x402-gradient { background: linear-gradient(135deg, #059669 0%, #10b981 50%, #34d399 100%); }
</style>
@endpush

@section('content')

    <!-- Hero -->
    <section class="x402-gradient text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="max-w-3xl">
                <div class="inline-flex items-center px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-sm mb-6">
                    <span class="w-2 h-2 bg-white rounded-full mr-2"></span>
                    v5.2.0 &middot; Production Ready
                </div>
                <h1 class="text-5xl font-bold mb-6">x402 Protocol</h1>
                <p class="text-xl text-emerald-100 mb-8">
                    HTTP-native micropayments for APIs. Return a 402, get paid in USDC, deliver data. No subscriptions, no API keys to manage &mdash; just pay-per-request built into HTTP.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('developers.show', 'api-docs') }}#x402" class="bg-white text-emerald-700 px-6 py-3 rounded-lg font-semibold hover:bg-emerald-50 transition">
                        API Reference
                    </a>
                    <a href="/api/documentation#/X402" target="_blank" class="border-2 border-white text-white px-6 py-3 rounded-lg font-semibold hover:bg-white hover:text-emerald-700 transition">
                        Swagger UI
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-900 text-center mb-4">How It Works</h2>
            <p class="text-gray-600 text-center mb-12 max-w-2xl mx-auto">Three HTTP round-trips. No out-of-band negotiation.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-emerald-600">1</span>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Request Resource</h3>
                    <p class="text-gray-600 text-sm mb-4">Client sends a standard HTTP request to a monetized endpoint.</p>
                    <div class="bg-gray-50 rounded-lg p-3 text-left font-mono text-xs text-gray-700">
                        <div>GET /v2/premium/data HTTP/1.1</div>
                        <div>Authorization: Bearer ...</div>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-amber-600">2</span>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">402 + Payment Info</h3>
                    <p class="text-gray-600 text-sm mb-4">Server responds with 402 and payment requirements in headers.</p>
                    <div class="bg-gray-50 rounded-lg p-3 text-left font-mono text-xs text-gray-700">
                        <div class="text-amber-600 font-semibold">HTTP/1.1 402 Payment Required</div>
                        <div>X-Payment-Amount: 0.01</div>
                        <div>X-Payment-Asset: USDC</div>
                        <div>X-Payment-Network: eip155:8453</div>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-green-600">3</span>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Pay & Receive Data</h3>
                    <p class="text-gray-600 text-sm mb-4">Client signs payment, retries with proof. Server settles and delivers.</p>
                    <div class="bg-gray-50 rounded-lg p-3 text-left font-mono text-xs text-gray-700">
                        <div class="text-green-600 font-semibold">HTTP/1.1 200 OK</div>
                        <div>X-Payment-Settled: true</div>
                        <div>Content-Type: application/json</div>
                        <div class="text-gray-400 mt-1">{"data": { ... }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Features -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">Key Features</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold mb-2">Payment Gate Middleware</h3>
                    <p class="text-gray-600 text-sm">Protect any route with x402. Configurable pricing, automatic 402 responses, and settlement verification.</p>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold mb-2">AI Agent Payments</h3>
                    <p class="text-gray-600 text-sm">Autonomous agents can pay for API access with budget limits, approval thresholds, and MCP tool integration.</p>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold mb-2">Spending Limits</h3>
                    <p class="text-gray-600 text-sm">Daily, per-transaction, and per-agent spending caps with approval-required thresholds above configurable amounts.</p>
                </div>

                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold mb-2">Multi-Network</h3>
                    <p class="text-gray-600 text-sm">Settle on Base (primary), Ethereum, or Avalanche. Configurable per-endpoint with automatic network detection.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Supported Networks -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-900 text-center mb-12">Supported Networks</h2>

            <div class="max-w-3xl mx-auto">
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Network</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Chain ID</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Asset</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">Base</td>
                                <td class="px-6 py-4 text-sm text-gray-600 font-mono">eip155:8453</td>
                                <td class="px-6 py-4 text-sm text-gray-600">USDC</td>
                                <td class="px-6 py-4"><span class="inline-flex items-center px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded-full font-medium">Primary</span></td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">Ethereum</td>
                                <td class="px-6 py-4 text-sm text-gray-600 font-mono">eip155:1</td>
                                <td class="px-6 py-4 text-sm text-gray-600">USDC</td>
                                <td class="px-6 py-4"><span class="inline-flex items-center px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full font-medium">Supported</span></td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">Avalanche</td>
                                <td class="px-6 py-4 text-sm text-gray-600 font-mono">eip155:43114</td>
                                <td class="px-6 py-4 text-sm text-gray-600">USDC</td>
                                <td class="px-6 py-4"><span class="inline-flex items-center px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full font-medium">Supported</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Developer Integration -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-gray-900 text-center mb-4">Developer Integration</h2>
            <p class="text-gray-600 text-center mb-12 max-w-2xl mx-auto">Protect any Laravel route with x402 middleware in one line.</p>

            <div class="max-w-3xl mx-auto">
                <x-code-block language="php">
// routes/api.php
Route::get('/premium/market-data', [MarketDataController::class, 'index'])
    ->middleware('x402:100000'); // Price in micro-USDC (= $0.10)

// Or with full configuration
Route::get('/premium/analytics', [AnalyticsController::class, 'show'])
    ->middleware('x402:500000,eip155:8453,USDC');
                </x-code-block>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-20 x402-gradient text-white">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold mb-4">Start Monetizing Your APIs</h2>
            <p class="text-lg text-emerald-100 mb-8">Add x402 payment gates to your endpoints and get paid per request.</p>
            <div class="flex flex-wrap gap-4 justify-center">
                <a href="{{ route('developers.show', 'api-docs') }}#x402" class="bg-white text-emerald-700 px-6 py-3 rounded-lg font-semibold hover:bg-emerald-50 transition">
                    Read the Docs
                </a>
                <a href="{{ route('developers') }}" class="border-2 border-white text-white px-6 py-3 rounded-lg font-semibold hover:bg-white hover:text-emerald-700 transition">
                    Developer Portal
                </a>
            </div>
        </div>
    </section>

@endsection

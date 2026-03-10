<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('brand.name') }} App — Pay with Stablecoins. Stay Private.</title>

    @include('partials.favicon')

    @include('partials.seo', [
        'title' => config('brand.name') . ' App — Pay with Stablecoins in Shops',
        'description' => 'Pay at any shop with your stablecoin card. Your transactions stay private. Your identity stays yours. Get early access to the ' . config('brand.name') . ' mobile wallet.',
        'keywords' => config('brand.name') . ', stablecoin wallet, USDC payments, privacy wallet, tap to pay crypto, shielded transactions, Super KYC',
    ])

    {{-- Fonts: Space Grotesk, JetBrains Mono, DM Sans --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=space-grotesk:300,400,500,600,700&dm-sans:400,500,600,700&jetbrains-mono:400,500,700&display=swap" rel="stylesheet" />

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    heading: ['"Space Grotesk"', 'system-ui', 'sans-serif'],
                    body: ['"DM Sans"', 'system-ui', 'sans-serif'],
                    mono: ['"JetBrains Mono"', 'monospace'],
                },
                colors: {
                    obsidian: '#0a0a0a',
                    acid: '#ccff00',
                    'acid-dark': '#a3cc00',
                    mint: '#a8f0c4',
                    'mint-mid': '#c0f5d6',
                    lavender: '#c8a8f0',
                    'z-blue': '#a8c8f0',
                    'z-purple': '#7000ff',
                    'z-green': '#10b981',
                    'z-gold': '#f59e0b',
                    'z-pink': '#f9a8d4',
                    'z-red': '#ef4444',
                    'text-sec': '#444444',
                    'text-muted': '#888888',
                    'bg-tertiary': '#f0f0f0',
                    'border-subtle': '#d1d5db',
                },
            }
        }
    }
    </script>

    @if(config('brand.ga_id'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('brand.ga_id') }}"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '{{ config('brand.ga_id') }}');
    </script>
    @endif

    <style>
        /* ── Base ── */
        html { scroll-behavior: smooth; }
        body {
            background: #ffffff;
            color: #0a0a0a;
            font-family: 'Space Grotesk', system-ui, sans-serif;
            overflow-x: hidden;
        }
        ::selection { background: #ccff00; color: #000; }

        /* ── Hard shadow utility ── */
        .hs-4 { box-shadow: 4px 4px 0px #0a0a0a; }
        .hs-6 { box-shadow: 6px 6px 0px #0a0a0a; }
        .hs-8 { box-shadow: 8px 8px 0px #0a0a0a; }

        /* ── Font shorthands ── */
        .font-hf { font-family: 'Space Grotesk', system-ui, sans-serif; letter-spacing: -0.04em; }
        .font-mono-z { font-family: 'JetBrains Mono', monospace; }
        .font-body-z { font-family: 'DM Sans', system-ui, sans-serif; }

        /* ── Animations ── */
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fade-in-left {
            from { opacity: 0; transform: translateX(-40px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes fade-in-right {
            from { opacity: 0; transform: translateY(30px) rotate(0deg); }
            to { opacity: 1; transform: translateY(0) rotate(3deg); }
        }
        @keyframes pop-in {
            from { opacity: 0; transform: scale(0); }
            to { opacity: 1; transform: scale(1); }
        }
        @keyframes z-marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .anim-fade-in-left { animation: fade-in-left 0.7s ease-out both; }
        .anim-fade-in-right { animation: fade-in-right 0.8s ease-out 0.2s both; }
        .anim-fade-in-up { animation: fade-in-up 0.6s ease-out both; }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f5f5f5; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.15); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.25); }

        /* ── Skip to content ── */
        .skip-to-content {
            position: absolute; top: -100%; left: 50%;
            transform: translateX(-50%);
            padding: 8px 16px; background: #ccff00; color: #000;
            font-weight: 600; border-radius: 0 0 8px 8px; z-index: 100;
            transition: top 0.2s;
        }
        .skip-to-content:focus { top: 0; }

        /* ── Feature tabs ── */
        .feature-panel { display: none; }
        .feature-panel.active { display: block; animation: fade-in-up 0.3s ease-out; }

        /* ── FAQ accordion ── */
        .faq-answer { max-height: 0; overflow: hidden; transition: max-height 0.3s ease, opacity 0.3s ease; opacity: 0; }
        .faq-answer.open { max-height: 300px; opacity: 1; }
        .faq-toggle { transition: transform 0.25s ease; }
        .faq-toggle.open { transform: rotate(45deg); }

        /* ── Button hover ── */
        .btn-hover { transition: transform 0.15s ease; }
        .btn-hover:hover { transform: scale(1.06); }
        .btn-hover:active { transform: scale(0.95); }

        /* ── Mobile menu ── */
        .mobile-menu { display: none; animation: fade-in-up 0.2s ease-out; }
        .mobile-menu.open { display: flex; }

        /* ── Phone scrollbar hide ── */
        .phone-scroll::-webkit-scrollbar { display: none; }
        .phone-scroll { scrollbar-width: none; }

        /* ── Sticker animations ── */
        .sticker { animation: pop-in 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) both; }
        .sticker-1 { animation-delay: 0.4s; }
        .sticker-2 { animation-delay: 0.6s; }
        .sticker-3 { animation-delay: 0.5s; }
        .sticker-4 { animation-delay: 0.7s; }

        /* ── Reduced motion ── */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
            html { scroll-behavior: auto; }
        }
    </style>
</head>
<body class="antialiased">

    <a href="#main-content" class="skip-to-content">Skip to content</a>

    {{-- ═══════════════════════════════════════════════════════════════
         NAVIGATION
    ═══════════════════════════════════════════════════════════════ --}}
    <nav class="fixed top-4 left-1/2 z-50 w-[95%] max-w-5xl -translate-x-1/2">
        <div class="flex items-center justify-between rounded-full px-4 py-2.5 md:px-6"
             style="background: rgba(255,255,255,0.92); border: 3px solid #0a0a0a; box-shadow: 4px 4px 0px #0a0a0a; backdrop-filter: blur(12px);">

            {{-- Logo --}}
            <div class="flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg text-lg font-black font-hf"
                     style="background: #a8f0c4; border: 3px solid #0a0a0a;">
                    Z
                </div>
                <span class="text-xl font-bold hidden sm:inline font-hf">{{ config('brand.name') }}</span>
            </div>

            {{-- Desktop links --}}
            <div class="hidden md:flex items-center gap-6">
                <a href="#features" class="text-sm font-medium text-text-sec hover:underline decoration-2 underline-offset-4">Features</a>
                <a href="#security" class="text-sm font-medium text-text-sec hover:underline decoration-2 underline-offset-4">Security</a>
                <a href="#faq" class="text-sm font-medium text-text-sec hover:underline decoration-2 underline-offset-4">FAQ</a>
            </div>

            {{-- Right CTA --}}
            <div class="flex items-center gap-3">
                <a href="#cta" class="btn-hover rounded-full px-5 py-2 text-sm font-bold"
                   style="background: #ccff00; border: 3px solid #0a0a0a; color: #0a0a0a;">
                    Early Access
                </a>

                {{-- Hamburger (mobile) --}}
                <button id="mobile-toggle" class="md:hidden flex flex-col gap-1.5 p-1" aria-label="toggle menu">
                    <span class="block h-0.5 w-5 rounded-full bg-obsidian transition-all"></span>
                    <span class="block h-0.5 w-5 rounded-full bg-obsidian transition-all"></span>
                    <span class="block h-0.5 w-5 rounded-full bg-obsidian transition-all"></span>
                </button>
            </div>
        </div>

        {{-- Mobile dropdown --}}
        <div id="mobile-menu" class="mobile-menu mt-2 flex-col gap-2 rounded-3xl p-5 md:hidden"
             style="background: rgba(255,255,255,0.95); border: 3px solid #0a0a0a; box-shadow: 4px 4px 0px #0a0a0a; backdrop-filter: blur(12px);">
            <a href="#features" class="text-base font-medium py-1 mobile-link">Features</a>
            <a href="#security" class="text-base font-medium py-1 mobile-link">Security</a>
            <a href="#faq" class="text-base font-medium py-1 mobile-link">FAQ</a>
        </div>
    </nav>


    <main id="main-content">

    {{-- ═══════════════════════════════════════════════════════════════
         HERO
    ═══════════════════════════════════════════════════════════════ --}}
    <section class="relative pt-28 pb-16 md:pt-36 md:pb-24 px-5"
             style="background: linear-gradient(150deg, #a8f0c4 0%, #c0f5d6 40%, #c8a8f0 100%);">
        <div class="mx-auto max-w-7xl grid grid-cols-1 md:grid-cols-2 gap-12 items-center">

            {{-- Left text --}}
            <div class="anim-fade-in-left">
                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black leading-[0.95] font-hf">
                    Secure Stablecoin <span class="text-z-purple">Payments</span>
                </h1>

                <p class="mt-6 text-lg md:text-xl max-w-lg font-medium font-body-z text-text-sec">
                    The stablecoin wallet that shields your on-chain tracks, ships virtual cards on demand,
                    and never asks you to trust a third party with your keys.
                </p>

                <div class="mt-8 flex flex-wrap gap-4">
                    <a href="#cta"
                       class="btn-hover rounded-full px-8 py-3.5 text-base font-bold hs-6"
                       style="background: #ccff00; border: 3px solid #0a0a0a; color: #0a0a0a;">
                        Get Early Access
                    </a>
                    <a href="#features"
                       class="btn-hover rounded-full px-8 py-3.5 text-base font-bold hs-6"
                       style="background: #ffffff; border: 3px solid #0a0a0a; color: #0a0a0a;">
                        How It Works
                    </a>
                </div>

                {{-- App store badges --}}
                <div class="mt-8">
                    <p class="mb-3 text-[10px] uppercase tracking-[0.3em] font-mono-z text-text-muted">Coming Soon to Mobile</p>
                    <div class="flex gap-3">
                        @foreach([['label' => 'App Store', 'sub' => 'Download on the'], ['label' => 'Google Play', 'sub' => 'Get it on']] as $store)
                        <div class="flex h-10 items-center gap-2 rounded-lg px-4 cursor-not-allowed opacity-50"
                             style="border: 2px solid rgba(10,10,10,0.12); background: rgba(255,255,255,0.5);">
                            <div>
                                <p class="text-[8px] text-text-muted">{{ $store['sub'] }}</p>
                                <p class="text-xs font-semibold text-text-sec">{{ $store['label'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Right — phone mockup --}}
            <div class="relative flex justify-center anim-fade-in-right">
                {{-- Floating stickers (desktop only) --}}
                <div class="absolute z-10 hidden md:block sticker sticker-1" style="top: -2%; left: -2%; transform: rotate(-12deg);">
                    <div class="rounded-2xl overflow-hidden hs-6" style="width: 64px; height: 64px; border: 3px solid #0a0a0a;">
                        <img src="/icons/lock.svg" alt="" class="h-full w-full">
                    </div>
                </div>
                <div class="absolute z-10 hidden md:block sticker sticker-2" style="top: 5%; right: -5%; transform: rotate(8deg);">
                    <div class="rounded-2xl overflow-hidden hs-6" style="width: 64px; height: 64px; border: 3px solid #0a0a0a;">
                        <img src="/icons/credit-card.svg" alt="" class="h-full w-full">
                    </div>
                </div>
                <div class="absolute z-10 hidden md:block sticker sticker-3" style="bottom: 18%; left: -5%; transform: rotate(-6deg);">
                    <div class="rounded-2xl overflow-hidden hs-6" style="width: 64px; height: 64px; border: 3px solid #0a0a0a;">
                        <img src="/icons/shield.svg" alt="" class="h-full w-full">
                    </div>
                </div>
                <div class="absolute z-10 hidden md:block sticker sticker-4" style="bottom: 2%; right: -2%; transform: rotate(15deg);">
                    <div class="rounded-2xl overflow-hidden hs-6" style="width: 64px; height: 64px; border: 3px solid #0a0a0a;">
                        <img src="/icons/checkmark.svg" alt="" class="h-full w-full">
                    </div>
                </div>

                {{-- Phone frame --}}
                <div class="relative w-[280px] md:w-[375px] overflow-hidden"
                     style="border: 6px solid #0a0a0a; border-radius: 2.5rem; box-shadow: 8px 8px 0px #0a0a0a; background: linear-gradient(180deg, #a8f0c4 0%, #c0f5d6 40%, #c8a8f0 100%); aspect-ratio: 9 / 19.5;">
                    <div class="flex h-full flex-col">

                        {{-- Dynamic island --}}
                        <div class="flex justify-center pt-3">
                            <div class="h-[24px] w-[90px] rounded-full bg-black"></div>
                        </div>

                        {{-- Status bar --}}
                        <div class="flex items-center justify-between px-6 py-1.5">
                            <span class="text-[11px] font-medium font-mono-z">9:41</span>
                            <div class="flex items-center gap-1.5">
                                <div class="flex items-end gap-[2px]">
                                    @foreach([6, 8, 10, 12] as $h)
                                    <div style="width: 3px; height: {{ $h }}px; border-radius: 1px; background: #0a0a0a; opacity: 0.4;"></div>
                                    @endforeach
                                </div>
                                <div style="width: 22px; height: 10px; border-radius: 3px; border: 1.5px solid rgba(0,0,0,0.4); position: relative; margin-left: 2px;">
                                    <div style="position: absolute; top: 2px; left: 2px; width: 14px; height: 4px; border-radius: 1px; background: rgba(0,0,0,0.4);"></div>
                                    <div style="position: absolute; right: -3px; top: 2.5px; width: 2px; height: 4px; border-radius: 0 1px 1px 0; background: rgba(0,0,0,0.4);"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Scrollable app content --}}
                        <div class="flex-1 overflow-y-auto phone-scroll">
                            <div class="px-5 pb-4 flex flex-col gap-4">

                                {{-- Header bar --}}
                                <div class="flex items-center justify-between pt-1">
                                    <div class="flex items-center gap-1.5 rounded-full" style="padding: 6px 12px; background: #fff; border: 3px solid #0a0a0a;">
                                        <div class="rounded-full" style="width: 8px; height: 8px; background: #10b981;"></div>
                                        <span class="font-mono-z" style="font-size: 14px; font-weight: 600;">Mainnet</span>
                                    </div>

                                    <div class="flex items-center" style="gap: 12px;">
                                        {{-- Streak --}}
                                        <div class="flex items-center" style="gap: 6px; padding: 6px 12px; background: rgba(245,158,11,0.12); border-radius: 9999px; border: 3px solid rgba(245,158,11,0.5);">
                                            <img src="/icons/flame.svg" alt="" style="width: 22px; height: 22px;">
                                            <span class="font-mono-z" style="font-size: 15px; font-weight: 700;">7</span>
                                        </div>

                                        {{-- Bell --}}
                                        <div style="position: relative;">
                                            <div class="flex items-center justify-center rounded-full" style="width: 48px; height: 48px; background: #fff; border: 3px solid #0a0a0a;">
                                                <img src="/icons/bell.svg" alt="" style="width: 30px; height: 30px;">
                                            </div>
                                            <div class="flex items-center justify-center rounded-full" style="position: absolute; top: -2px; right: -4px; width: 18px; height: 18px; background: #ef4444;">
                                                <span style="font-size: 10px; font-weight: 700; color: #fff;">7</span>
                                            </div>
                                        </div>

                                        {{-- Avatar --}}
                                        <div class="flex items-center justify-center rounded-full"
                                             style="width: 48px; height: 48px; background: linear-gradient(135deg, #a8f0c4, #c8a8f0); border: 3px solid #0a0a0a; box-shadow: 3px 3px 0px #0a0a0a;">
                                            <span class="font-hf" style="font-size: 18px; font-weight: 700; color: #fff;">A</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Balance card --}}
                                <div class="rounded-[20px]" style="padding: 20px; background: linear-gradient(135deg, #a8f0c4, #a8c8f0); border: 3px solid #0a0a0a; box-shadow: 6px 6px 0px #0a0a0a;">
                                    <div class="flex items-start justify-between">
                                        <div class="flex flex-col gap-1">
                                            <div class="flex items-center" style="gap: 8px;">
                                                <span class="font-body-z" style="font-size: 14px; font-weight: 500; color: #444;">Total Balance</span>
                                                <div class="flex items-center rounded-full" style="gap: 4px; padding: 3px 8px; background: #a8f0c4; border: 3px solid #0a0a0a;">
                                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="#0a0a0a" xmlns="http://www.w3.org/2000/svg"><path d="M12 2L3 7v5c0 5.5 3.8 10.7 9 12 5.2-1.3 9-6.5 9-12V7l-9-5Z"/></svg>
                                                    <span class="font-mono-z" style="font-size: 11px; font-weight: 700;">PROTECTED</span>
                                                </div>
                                            </div>
                                            <div class="flex items-baseline" style="gap: 4px;">
                                                <span style="font-size: 36px; font-weight: 700; font-variant-numeric: tabular-nums; letter-spacing: -0.02em;">$1,248.35</span>
                                                <span class="font-mono-z" style="font-size: 14px; font-weight: 700; color: #444;">USDC</span>
                                            </div>
                                            <span class="font-mono-z" style="font-size: 14px; color: #888;">USDC on Solana</span>
                                        </div>
                                        <div class="flex items-center justify-center" style="width: 44px; height: 44px; background: #f0f0f0; border-radius: 10px; border: 1px solid #d1d5db;">
                                            <img src="/icons/qr-code.svg" alt="" style="width: 28px; height: 28px;">
                                        </div>
                                    </div>
                                    <div class="flex items-center" style="gap: 8px; margin-top: 16px;">
                                        <span class="font-mono-z" style="font-size: 11px; color: #888;">WALLET ID</span>
                                        <span class="font-mono-z" style="font-size: 14px; color: #444;">8xR9k&hellip;4f2Z</span>
                                        <div style="flex: 1;"></div>
                                        <span class="font-mono-z" style="font-size: 11px; color: #7000ff; font-weight: 500;">Copy</span>
                                    </div>
                                </div>

                                {{-- Quick actions --}}
                                <div class="flex justify-center" style="gap: 16px;">
                                    @foreach([['svg' => '/icons/qr-code.svg', 'label' => 'Pay'], ['svg' => '/icons/arrow-down-left.svg', 'label' => 'Receive'], ['svg' => '/icons/arrow-up-right.svg', 'label' => 'Send']] as $action)
                                    <div class="flex flex-col items-center" style="gap: 8px;">
                                        <div class="flex items-center justify-center rounded-full overflow-hidden" style="width: 56px; height: 56px; border: 3px solid #0a0a0a; box-shadow: 3px 3px 0px #0a0a0a;">
                                            <img src="{{ $action['svg'] }}" alt="" style="width: 100%; height: 100%;">
                                        </div>
                                        <span class="font-body-z" style="font-size: 14px; font-weight: 500; color: #444;">{{ $action['label'] }}</span>
                                    </div>
                                    @endforeach
                                </div>

                                {{-- Security status --}}
                                <div class="flex flex-col" style="gap: 12px;">
                                    <span class="font-hf" style="font-size: 20px; font-weight: 700;">Security Status</span>
                                    <div class="flex" style="gap: 12px;">
                                        @foreach([['icon' => '/icons/shield.svg', 'title' => 'Shielded', 'sub' => 'Privacy Active'], ['icon' => '/icons/shield-check.svg', 'title' => 'Verified', 'sub' => 'On-chain Identity']] as $card)
                                        <div class="flex items-center flex-1" style="gap: 12px; padding: 14px; background: #fff; border: 3px solid #0a0a0a; border-radius: 14px; box-shadow: 6px 6px 0px #0a0a0a;">
                                            <div class="overflow-hidden" style="width: 44px; height: 44px; border-radius: 10px; border: 3px solid #0a0a0a; flex-shrink: 0;">
                                                <img src="{{ $card['icon'] }}" alt="" style="width: 100%; height: 100%;">
                                            </div>
                                            <div>
                                                <p class="font-body-z" style="font-size: 14px; font-weight: 700;">{{ $card['title'] }}</p>
                                                <p class="font-body-z" style="font-size: 13px; color: #888;">{{ $card['sub'] }}</p>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Recent activity --}}
                                <div class="flex flex-col" style="gap: 16px;">
                                    <div class="flex items-center justify-between">
                                        <span class="font-hf" style="font-size: 20px; font-weight: 700;">Recent Activity</span>
                                        <span class="font-body-z" style="font-size: 14px; font-weight: 500; color: #7000ff;">See All</span>
                                    </div>

                                    <div class="overflow-hidden" style="background: #fff; border: 3px solid #0a0a0a; border-radius: 16px; box-shadow: 6px 6px 0px #0a0a0a;">
                                        @php
                                        $transactions = [
                                            ['svg' => '/icons/credit-card.svg', 'name' => 'Merchant Payment', 'date' => 'Today 3:41 PM', 'amount' => '-$42.50', 'pos' => false, 'shielded' => true],
                                            ['svg' => '/icons/arrow-up-right.svg', 'name' => 'Transfer to Wallet', 'date' => 'Yesterday 2:15 PM', 'amount' => '-$14.99', 'pos' => false, 'shielded' => false],
                                            ['svg' => '/icons/arrow-down-left.svg', 'name' => 'USDC Received', 'date' => 'Yesterday 11:30 AM', 'amount' => '+$500.00', 'pos' => true, 'shielded' => false],
                                            ['svg' => '/icons/credit-card.svg', 'name' => 'Starbolt Coffee', 'date' => 'Mar 5 9:20 AM', 'amount' => '-$12.00', 'pos' => false, 'shielded' => true],
                                        ];
                                        @endphp
                                        @foreach($transactions as $i => $tx)
                                        <div>
                                            <div class="flex items-center" style="padding: 12px 16px; gap: 14px;">
                                                <div class="rounded-full overflow-hidden" style="width: 44px; height: 44px; border: 1px solid #d1d5db; flex-shrink: 0;">
                                                    <img src="{{ $tx['svg'] }}" alt="" style="width: 100%; height: 100%;">
                                                </div>
                                                <div style="flex: 1; min-width: 0;">
                                                    <p class="font-body-z" style="font-size: 15px; font-weight: 500; margin: 0; line-height: 1.3;">{{ $tx['name'] }}</p>
                                                    <div class="flex items-center" style="gap: 6px; margin-top: 2px;">
                                                        <span class="font-body-z" style="font-size: 13px; color: #888;">{{ $tx['date'] }}</span>
                                                        <div class="rounded-full" style="width: 5px; height: 5px; background: #10b981;"></div>
                                                        <span class="font-body-z" style="font-size: 11px; color: #10b981;">Confirmed</span>
                                                    </div>
                                                </div>
                                                <div class="flex flex-col items-end flex-shrink-0" style="gap: 2px;">
                                                    <span style="font-size: 14px; font-weight: 600; font-variant-numeric: tabular-nums; color: {{ $tx['pos'] ? '#10b981' : '#0a0a0a' }};">{{ $tx['amount'] }}</span>
                                                    @if($tx['shielded'])
                                                    <div class="flex items-center" style="gap: 3px;">
                                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="#10b981" xmlns="http://www.w3.org/2000/svg"><path d="M12 2L3 7v5c0 5.5 3.8 10.7 9 12 5.2-1.3 9-6.5 9-12V7l-9-5Z"/></svg>
                                                        <span class="font-mono-z" style="font-size: 10px; color: #10b981;">Protected</span>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($i < count($transactions) - 1)
                                            <div style="height: 1px; background: #d1d5db; margin-left: 16px; margin-right: 16px;"></div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- Bottom tab bar --}}
                        <div class="flex items-center justify-around" style="padding: 8px; border-top: 3px solid #0a0a0a; background: #fff;">
                            @php
                            $tabs = [
                                ['svg' => '/icons/home.svg', 'label' => 'Home', 'active' => true],
                                ['svg' => '/icons/clock.svg', 'label' => 'Activity', 'active' => false],
                                ['svg' => null, 'label' => 'Pay', 'active' => false],
                                ['svg' => '/icons/star.svg', 'label' => 'Rewards', 'active' => false],
                                ['svg' => '/icons/gear.svg', 'label' => 'Settings', 'active' => false],
                            ];
                            @endphp
                            @foreach($tabs as $tab)
                                @if($tab['svg'])
                                <div class="flex flex-col items-center" style="gap: 4px; padding: 4px 0; flex: 1;">
                                    <img src="{{ $tab['svg'] }}" alt="" style="width: 26px; height: 26px; opacity: {{ $tab['active'] ? '1' : '0.4' }};">
                                    <span class="font-mono-z" style="font-size: 11px; color: {{ $tab['active'] ? '#0a0a0a' : '#888' }};">{{ $tab['label'] }}</span>
                                </div>
                                @else
                                <div class="flex items-center justify-center rounded-full"
                                     style="width: 56px; height: 56px; background: #ccff00; border: 3px solid #0a0a0a; box-shadow: 4px 4px 0px #0a0a0a; margin-top: -16px;">
                                    <img src="/icons/qr-code.svg" alt="" style="width: 36px; height: 36px;">
                                </div>
                                @endif
                            @endforeach
                        </div>

                        {{-- Home indicator --}}
                        <div class="flex justify-center" style="padding: 6px 0; background: #fff;">
                            <div class="rounded-full" style="height: 4px; width: 96px; background: #f0f0f0;"></div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        {{-- Bottom border --}}
        <div class="absolute bottom-0 left-0 right-0 h-1 bg-obsidian"></div>
    </section>


    {{-- ═══════════════════════════════════════════════════════════════
         MARQUEE
    ═══════════════════════════════════════════════════════════════ --}}
    <section class="relative py-4 overflow-hidden bg-obsidian"
             style="border-top: 4px solid #fff; border-bottom: 4px solid #fff; transform: rotate(-1.5deg) scale(1.02);">
        <div class="flex whitespace-nowrap" style="animation: z-marquee 20s linear infinite;">
            @for($dup = 0; $dup < 2; $dup++)
            <span class="text-white text-xl md:text-2xl font-black uppercase tracking-wider font-hf pr-8">
                SECURE STABLECOINS <span class="text-acid">&#9670;</span> SHIELD PRIVACY
                <span class="text-acid">&#9670;</span> VIRTUAL CARDS
                <span class="text-acid">&#9670;</span> SPLIT KEY SECURITY
                <span class="text-acid">&#9670;</span> ZERO TRACE
                <span class="text-acid">&#9670;</span> SECURE STABLECOINS
                <span class="text-acid">&#9670;</span> SHIELD PRIVACY
                <span class="text-acid">&#9670;</span> VIRTUAL CARDS
                <span class="text-acid">&#9670;</span> SPLIT KEY SECURITY
                <span class="text-acid">&#9670;</span> ZERO TRACE
                <span class="text-acid">&#9670;</span>
            </span>
            @endfor
        </div>
    </section>


    {{-- ═══════════════════════════════════════════════════════════════
         FEATURES
    ═══════════════════════════════════════════════════════════════ --}}
    <section id="features" class="px-5 py-20 md:py-28">
        <div class="mx-auto max-w-6xl">
            <h2 class="text-4xl md:text-5xl font-black mb-4 font-hf anim-fade-in-up">Built Different.</h2>
            <p class="text-lg mb-10 max-w-xl text-text-sec">
                Everything you need for secure stablecoin payments — nothing you don&apos;t.
            </p>

            {{-- Tab bar --}}
            <div class="flex gap-2 mb-8 flex-wrap" id="feature-tabs">
                @foreach(['Pay', 'Core', 'Security', 'Shield'] as $tab)
                <button onclick="switchTab('{{ strtolower($tab) }}')"
                        class="feature-tab rounded-full px-5 py-2 text-sm font-bold transition-all duration-200"
                        data-tab="{{ strtolower($tab) }}"
                        style="background: {{ $tab === 'Core' ? '#ccff00' : '#f0f0f0' }}; border: 3px solid {{ $tab === 'Core' ? '#0a0a0a' : '#f0f0f0' }}; color: #0a0a0a;">
                    {{ $tab }}
                </button>
                @endforeach
            </div>

            {{-- Core panel --}}
            <div class="feature-panel active p-8 md:p-10 mb-6" id="panel-core"
                 style="background: #a8c8f0; border: 3px solid #0a0a0a; border-radius: 2rem; box-shadow: 6px 6px 0px #0a0a0a;">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                    <div>
                        <h3 class="text-2xl md:text-3xl font-black mb-2 font-hf">Your Keys, Split Three Ways</h3>
                        <p class="text-base text-text-sec">
                            Your private key is split into 3 encrypted shards. No single device or server ever holds the full key.
                        </p>
                    </div>
                    <div class="flex items-center justify-center gap-3 md:gap-4 flex-wrap">
                        @foreach([['icon' => '/icons/smartphone.svg', 'label' => 'Device'], ['icon' => '/icons/lock.svg', 'label' => 'Enclave'], ['icon' => '/icons/server.svg', 'label' => 'Server'], ['icon' => '/icons/checkmark.svg', 'label' => 'Signed']] as $i => $node)
                        <div class="flex items-center gap-3 md:gap-4">
                            <div class="flex flex-col items-center">
                                <div class="w-[64px] h-[64px] md:w-[80px] md:h-[80px] rounded-full overflow-hidden" style="border: 3px solid #0a0a0a; box-shadow: 3px 3px 0px #0a0a0a;">
                                    <img src="{{ $node['icon'] }}" alt="" class="h-full w-full">
                                </div>
                                <span class="text-[10px] md:text-xs font-bold mt-1.5 font-mono-z text-text-sec">{{ $node['label'] }}</span>
                            </div>
                            @if($i < 3)
                            <span class="text-lg font-bold" style="color: #0a0a0a; margin-top: -1.2rem;">&rarr;</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Pay panel --}}
            <div class="feature-panel p-8 md:p-10 mb-6" id="panel-pay"
                 style="background: #f9a8d4; border: 3px solid #0a0a0a; border-radius: 2rem; box-shadow: 6px 6px 0px #0a0a0a;">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                    <div>
                        <h3 class="text-2xl md:text-3xl font-black mb-2 font-hf">Pay with Your Card</h3>
                        <p class="text-base text-text-sec">
                            Spin up virtual Visa cards instantly. Spend stablecoins at any merchant worldwide — they see a normal card payment, not a crypto wallet.
                        </p>
                    </div>
                    <div class="flex justify-center">
                        <div class="p-5 w-full max-w-[280px]" style="background: #0a0a0a; border-radius: 1.5rem; border: 3px solid #0a0a0a; transform: rotate(-2deg);">
                            <p class="text-white/50 text-xs uppercase tracking-wider">{{ config('brand.name') }} Virtual</p>
                            <p class="text-white text-lg font-bold mt-1 tracking-widest font-mono-z">&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; 4291</p>
                            <div class="flex justify-between mt-4">
                                <span class="text-white/40 text-xs">Valid thru 12/28</span>
                                <span class="text-white/40 text-xs uppercase">Visa</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Security panel --}}
            <div id="security"></div>
            <div class="feature-panel p-8 md:p-10 mb-6" id="panel-security"
                 style="background: #a8f0c4; border: 3px solid #0a0a0a; border-radius: 2rem; box-shadow: 6px 6px 0px #0a0a0a;">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                    <div>
                        <h3 class="text-2xl md:text-3xl font-black mb-2 font-hf">Biometric Authentication</h3>
                        <p class="text-base text-text-sec">
                            Face ID and passkey support. Your wallet is protected by the same biometric security as your device — no passwords to remember or lose.
                        </p>
                    </div>
                    <div class="flex gap-4 justify-center">
                        @foreach(['/icons/fingerprint.svg', '/icons/user.svg', '/icons/shield.svg'] as $src)
                        <div class="w-[72px] h-[72px] md:w-[88px] md:h-[88px] rounded-full overflow-hidden" style="border: 3px solid #0a0a0a; box-shadow: 4px 4px 0px #0a0a0a;">
                            <img src="{{ $src }}" alt="" class="h-full w-full">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Shield panel --}}
            <div class="feature-panel p-8 md:p-10 mb-6" id="panel-shield"
                 style="background: #c8a8f0; border: 3px solid #0a0a0a; border-radius: 2rem; box-shadow: 6px 6px 0px #0a0a0a;">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                    <div>
                        <h3 class="text-2xl md:text-3xl font-black mb-2 font-hf">Shield Your Transactions</h3>
                        <p class="text-base text-text-sec">
                            Your on-chain history is decoupled from your spending. Privacy relayers ensure no one traces your purchases back to your wallet.
                        </p>
                    </div>
                    <div class="flex gap-4 justify-center">
                        @foreach(['/icons/ghost.svg', '/icons/incognito.svg', '/icons/globe.svg'] as $src)
                        <div class="w-[72px] h-[72px] md:w-[88px] md:h-[88px] rounded-full overflow-hidden" style="border: 3px solid #0a0a0a; box-shadow: 4px 4px 0px #0a0a0a;">
                            <img src="{{ $src }}" alt="" class="h-full w-full">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 2-column sub-cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="p-8" style="background: #fff; border: 3px solid #0a0a0a; border-radius: 2rem; box-shadow: 6px 6px 0px #0a0a0a;">
                    <div class="rounded-full overflow-hidden mb-4" style="width: 56px; height: 56px; border: 3px solid #0a0a0a; box-shadow: 3px 3px 0px #0a0a0a;">
                        <img src="/icons/credit-card.svg" alt="" class="h-full w-full">
                    </div>
                    <h3 class="text-xl md:text-2xl font-black mb-2 font-hf">Virtual Cards</h3>
                    <p class="text-base text-text-sec">
                        Generate disposable virtual Visa cards linked to your stablecoin balance. Merchants see a normal card payment.
                    </p>
                </div>

                <div class="p-8" style="background: #fff; border: 3px solid #0a0a0a; border-radius: 2rem; box-shadow: 6px 6px 0px #0a0a0a;">
                    <div class="rounded-full overflow-hidden mb-4" style="width: 56px; height: 56px; border: 3px solid #0a0a0a; box-shadow: 3px 3px 0px #0a0a0a;">
                        <img src="/icons/incognito.svg" alt="" class="h-full w-full">
                    </div>
                    <h3 class="text-xl md:text-2xl font-black mb-2 font-hf">Privacy Relayers</h3>
                    <p class="text-base text-text-sec">
                        Transactions routed through privacy-preserving relayers. Your blockchain address stays disconnected from purchases.
                    </p>
                </div>
            </div>
        </div>
    </section>


    {{-- ═══════════════════════════════════════════════════════════════
         FAQ
    ═══════════════════════════════════════════════════════════════ --}}
    <section id="faq" class="px-5 py-20 md:py-28 bg-bg-tertiary">
        <div class="mx-auto max-w-3xl">
            <h2 class="text-4xl md:text-5xl font-black mb-10 text-center font-hf">FAQ</h2>

            <div class="flex flex-col gap-4" id="faq-list">
                @php
                $faqs = [
                    ['q' => 'Is ' . config('brand.name') . ' a custodial wallet?', 'a' => 'No. ' . config('brand.name') . ' is fully non-custodial. Your keys, your coins. We never hold your funds — the wallet uses split-key architecture so only you can authorise transactions.'],
                    ['q' => 'How does transaction shielding work?', 'a' => config('brand.name') . ' routes your stablecoin transactions through privacy-preserving relayers. Your on-chain history is decoupled from your spending identity, so merchants and observers can\'t link your wallet to your purchases.'],
                    ['q' => 'Do I pay gas fees?', 'a' => 'Gas fees are abstracted away. You pay a flat micro-fee in the stablecoin you\'re spending — no ETH or native tokens needed. We batch and optimise under the hood.'],
                    ['q' => 'What is Super KYC?', 'a' => 'Super KYC is our one-time premium verification. Once you pass it you unlock higher spending limits, fiat off-ramps, and the physical metal card — all while your identity stays encrypted.'],
                ];
                @endphp
                @foreach($faqs as $i => $faq)
                <div class="overflow-hidden" style="border: 3px solid #0a0a0a; border-radius: 1.5rem; box-shadow: 6px 6px 0px #0a0a0a; background: #fff;">
                    <button onclick="toggleFaq({{ $i }})" class="w-full flex items-center justify-between px-6 py-5 text-left">
                        <span class="text-lg md:text-xl font-bold pr-4 font-hf">{{ $faq['q'] }}</span>
                        <span class="faq-toggle text-2xl font-black flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-xl" id="faq-icon-{{ $i }}"
                              style="background: #f0f0f0; border: 3px solid #0a0a0a;">+</span>
                    </button>
                    <div class="faq-answer" id="faq-{{ $i }}">
                        <div class="px-6 pb-5 text-base text-text-sec" style="border-top: 2px solid #e5e5e5;">
                            <p class="pt-4">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>


    {{-- ═══════════════════════════════════════════════════════════════
         CTA
    ═══════════════════════════════════════════════════════════════ --}}
    <section id="cta" class="relative px-5 py-20 md:py-28 overflow-hidden bg-acid">
        <div class="relative z-10 mx-auto max-w-3xl text-center">
            <h2 class="text-4xl sm:text-5xl md:text-6xl font-black mb-6 font-hf" style="transform: rotate(-2deg);">
                Ready to Go Off-Grid?
            </h2>
            <p class="text-lg md:text-xl mb-10 max-w-xl mx-auto text-text-sec">
                Drop your email and we&apos;ll send you an invite when the beta launches. No spam, just alpha.
            </p>

            <form id="early-access-form" class="flex flex-col sm:flex-row gap-3 max-w-lg mx-auto">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="email" name="email" required placeholder="your@email.com"
                       class="flex-1 px-6 py-4 text-base outline-none placeholder:text-black/30"
                       style="background: #fff; border: 3px solid #0a0a0a; border-radius: 9999px; box-shadow: 4px 4px 0px #0a0a0a;">
                <button type="submit"
                        class="btn-hover px-8 py-4 text-base font-bold rounded-full text-white"
                        style="background: #0a0a0a; border: 3px solid #0a0a0a; box-shadow: 4px 4px 0px #0a0a0a;">
                    Get Early Access
                </button>
            </form>

            <div id="early-access-success" class="hidden">
                <div class="inline-block px-8 py-5 rounded-full text-lg font-bold hs-6"
                     style="background: #fff; border: 3px solid #0a0a0a; color: #0a0a0a;">
                    You&apos;re on the list!
                </div>
            </div>
        </div>
    </section>

    </main>

    {{-- ═══════════════════════════════════════════════════════════════
         FOOTER
    ═══════════════════════════════════════════════════════════════ --}}
    <footer class="relative px-5 pt-12 pb-20 md:pt-16 md:pb-28 overflow-hidden" style="background: #fff; border-top: 8px solid #0a0a0a;">
        {{-- Giant watermark --}}
        <span class="absolute left-1/2 -translate-x-1/2 pointer-events-none select-none font-black font-hf"
              style="font-size: 10vw; opacity: 0.04; line-height: 1; white-space: nowrap; bottom: 1.5rem;">
            {{ config('brand.name') }}
        </span>

        <div class="relative z-10 mx-auto max-w-6xl flex flex-col md:flex-row items-start md:items-center justify-between gap-8">
            {{-- Logo --}}
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl text-xl font-black font-hf"
                     style="background: #a8f0c4; border: 3px solid #0a0a0a; box-shadow: 4px 4px 0px #0a0a0a;">
                    Z
                </div>
                <span class="text-2xl font-black font-hf">{{ config('brand.name') }}</span>
            </div>

            {{-- Center CTA --}}
            <a href="#cta" class="btn-hover rounded-full px-6 py-2.5 text-sm font-bold"
               style="background: #ccff00; border: 3px solid #0a0a0a; color: #0a0a0a;">
                Early Access
            </a>

            {{-- Links --}}
            <div class="flex flex-wrap gap-x-8 gap-y-3">
                <a href="#features" class="text-sm font-medium opacity-60 hover:opacity-100 transition-opacity">Features</a>
                <a href="#security" class="text-sm font-medium opacity-60 hover:opacity-100 transition-opacity">Security</a>
                <a href="#faq" class="text-sm font-medium opacity-60 hover:opacity-100 transition-opacity">FAQ</a>
            </div>
        </div>

        <div class="relative z-10 mx-auto max-w-6xl mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4"
             style="border-top: 2px solid #e5e5e5;">
            <p class="text-sm opacity-40">&copy; {{ date('Y') }} {{ config('brand.name') }}. All rights reserved.</p>
            <div class="flex gap-6">
                <a href="#" class="text-sm opacity-40 hover:opacity-100 transition-opacity">Privacy</a>
                <a href="#" class="text-sm opacity-40 hover:opacity-100 transition-opacity">Terms</a>
            </div>
        </div>
    </footer>


    {{-- ═══════════════════════════════════════════════════════════════
         SCRIPTS
    ═══════════════════════════════════════════════════════════════ --}}
    <script>
        // ── Mobile menu toggle ──
        const mobileToggle = document.getElementById('mobile-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        if (mobileToggle && mobileMenu) {
            mobileToggle.addEventListener('click', () => {
                mobileMenu.classList.toggle('open');
            });
            document.querySelectorAll('.mobile-link').forEach(link => {
                link.addEventListener('click', () => mobileMenu.classList.remove('open'));
            });
        }

        // ── Feature tabs ──
        function switchTab(tabName) {
            document.querySelectorAll('.feature-panel').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.feature-tab').forEach(t => {
                t.style.background = '#f0f0f0';
                t.style.borderColor = '#f0f0f0';
            });

            const panel = document.getElementById('panel-' + tabName);
            const tab = document.querySelector('[data-tab="' + tabName + '"]');
            if (panel) panel.classList.add('active');
            if (tab) {
                tab.style.background = '#ccff00';
                tab.style.borderColor = '#0a0a0a';
            }
        }

        // ── FAQ accordion ──
        function toggleFaq(index) {
            const answer = document.getElementById('faq-' + index);
            const icon = document.getElementById('faq-icon-' + index);
            const isOpen = answer.classList.contains('open');

            // close all
            document.querySelectorAll('.faq-answer').forEach(a => a.classList.remove('open'));
            document.querySelectorAll('.faq-toggle').forEach(t => {
                t.classList.remove('open');
                t.style.background = '#f0f0f0';
            });

            if (!isOpen) {
                answer.classList.add('open');
                icon.classList.add('open');
                icon.style.background = '#ccff00';
            }
        }

        // ── Early access form ──
        const eaForm = document.getElementById('early-access-form');
        const eaSuccess = document.getElementById('early-access-success');
        if (eaForm) {
            eaForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const email = eaForm.querySelector('input[name="email"]').value;
                if (!email) return;
                try {
                    await fetch('/subscriber/landing', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ email }),
                    });
                } catch { /* silently ignore */ }
                eaForm.style.display = 'none';
                eaSuccess.classList.remove('hidden');
            });
        }
    </script>

</body>
</html>

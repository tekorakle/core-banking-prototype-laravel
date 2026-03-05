<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Ramp Provider
    |--------------------------------------------------------------------------
    */

    'default_provider' => env('RAMP_PROVIDER', 'mock'),

    /*
    |--------------------------------------------------------------------------
    | Provider Configuration
    |--------------------------------------------------------------------------
    */

    'providers' => [
        'mock' => [
            'driver'  => 'mock',
            'enabled' => true,
        ],

        'onramper' => [
            'driver'               => 'onramper',
            'api_key'              => env('ONRAMPER_API_KEY'),
            'secret_key'           => env('ONRAMPER_SECRET_KEY'),
            'base_url'             => env('ONRAMPER_BASE_URL', 'https://api.onramper.com'),
            'widget_url'           => env('ONRAMPER_WIDGET_URL', 'https://buy.onramper.com'),
            'success_redirect_url' => env('ONRAMPER_SUCCESS_REDIRECT_URL'),
            'failure_redirect_url' => env('ONRAMPER_FAILURE_REDIRECT_URL'),
            'enabled'              => (bool) env('ONRAMPER_ENABLED', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Supported Currencies
    |--------------------------------------------------------------------------
    */

    'supported_fiat'   => ['USD', 'EUR', 'GBP'],
    'supported_crypto' => ['USDC', 'USDT', 'ETH', 'BTC'],

    /*
    |--------------------------------------------------------------------------
    | Transaction Limits
    |--------------------------------------------------------------------------
    */

    'limits' => [
        'min_fiat_amount' => (float) env('RAMP_MIN_AMOUNT', 10.00),
        'max_fiat_amount' => (float) env('RAMP_MAX_AMOUNT', 10000.00),
        'daily_limit'     => (float) env('RAMP_DAILY_LIMIT', 50000.00),
    ],

    /*
    |--------------------------------------------------------------------------
    | Referral Share Copy
    |--------------------------------------------------------------------------
    | Customizable by marketing. Use {code} placeholder for referral code.
    */

    'referral_share_text' => env(
        'REFERRAL_SHARE_TEXT',
        'Join FinAegis with my code {code} and earn free transactions!'
    ),
];

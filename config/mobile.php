<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Mobile App Version Configuration
    |--------------------------------------------------------------------------
    |
    | These settings control version requirements for the mobile app.
    | The app will prompt users to update if their version is below min_version.
    |
    */

    'min_version'    => env('MOBILE_MIN_VERSION', '1.0.0'),
    'latest_version' => env('MOBILE_LATEST_VERSION', '1.0.0'),
    'force_update'   => env('MOBILE_FORCE_UPDATE', false),

    /*
    |--------------------------------------------------------------------------
    | Device Limits
    |--------------------------------------------------------------------------
    |
    | Maximum number of devices a user can register.
    |
    */

    'max_devices_per_user' => env('MOBILE_MAX_DEVICES', 5),

    /*
    |--------------------------------------------------------------------------
    | Session Configuration
    |--------------------------------------------------------------------------
    |
    | Session duration settings for mobile authentication.
    |
    */

    'session' => [
        'duration_minutes'                => env('MOBILE_SESSION_DURATION', 60),
        'trusted_device_duration_minutes' => env('MOBILE_TRUSTED_SESSION_DURATION', 480),
        'biometric_challenge_ttl_seconds' => env('MOBILE_BIOMETRIC_CHALLENGE_TTL', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Push Notification Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for push notification behavior.
    |
    */

    'push' => [
        'enabled'             => env('MOBILE_PUSH_ENABLED', true),
        'max_retries'         => env('MOBILE_PUSH_MAX_RETRIES', 3),
        'retry_delay_seconds' => env('MOBILE_PUSH_RETRY_DELAY', 60),
        'batch_size'          => env('MOBILE_PUSH_BATCH_SIZE', 100),
        'cleanup_days'        => env('MOBILE_PUSH_CLEANUP_DAYS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific mobile features.
    |
    */

    'features' => [
        'biometric'     => env('MOBILE_FEATURE_BIOMETRIC', true),
        'push'          => env('MOBILE_FEATURE_PUSH', true),
        'gcu_trading'   => env('MOBILE_FEATURE_GCU_TRADING', true),
        'p2p_transfers' => env('MOBILE_FEATURE_P2P_TRANSFERS', true),
        'exchange'      => env('MOBILE_FEATURE_EXCHANGE', true),
        'lending'       => env('MOBILE_FEATURE_LENDING', false),
        'stablecoins'   => env('MOBILE_FEATURE_STABLECOINS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Security-related settings for mobile authentication.
    |
    */

    'security' => [
        // Days of inactivity before a device is considered stale
        'stale_device_days' => env('MOBILE_STALE_DEVICE_DAYS', 90),

        // Maximum failed biometric attempts before blocking
        'max_biometric_failures' => env('MOBILE_MAX_BIOMETRIC_FAILURES', 5),

        // Block duration in minutes after max failures
        'biometric_block_minutes' => env('MOBILE_BIOMETRIC_BLOCK_MINUTES', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Android Notification Channels
    |--------------------------------------------------------------------------
    |
    | Android notification channel configuration.
    |
    */

    'android_channels' => [
        'default' => [
            'id'          => 'finaegis_default',
            'name'        => 'General Notifications',
            'description' => 'General notifications from FinAegis',
            'importance'  => 'default',
        ],
        'transactions' => [
            'id'          => 'finaegis_transactions',
            'name'        => 'Transaction Alerts',
            'description' => 'Notifications about your transactions',
            'importance'  => 'high',
        ],
        'security' => [
            'id'          => 'finaegis_security',
            'name'        => 'Security Alerts',
            'description' => 'Important security notifications',
            'importance'  => 'high',
        ],
    ],
];

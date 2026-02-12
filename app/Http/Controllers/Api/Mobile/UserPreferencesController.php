<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserPreferencesController extends Controller
{
    private const DEFAULTS = [
        'activeNetwork'           => 'solana',
        'isPrivacyModeEnabled'    => true,
        'autoLockEnabled'         => true,
        'transactionAuthRequired' => true,
        'hideBalances'            => false,
        'poiEnabled'              => true,
        'biometricLockEnabled'    => true,
    ];

    private const VALIDATION_RULES = [
        'activeNetwork'           => 'string|max:50',
        'isPrivacyModeEnabled'    => 'boolean',
        'autoLockEnabled'         => 'boolean',
        'transactionAuthRequired' => 'boolean',
        'hideBalances'            => 'boolean',
        'poiEnabled'              => 'boolean',
        'biometricLockEnabled'    => 'boolean',
    ];

    /**
     * Get the authenticated user's mobile preferences.
     *
     * GET /api/v1/user/preferences
     */
    public function show(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        /** @var array<string, mixed> $stored */
        $stored = $user->mobile_preferences ?? [];
        $merged = array_merge(self::DEFAULTS, $stored);

        return response()->json([
            'success' => true,
            'data'    => $merged,
        ]);
    }

    /**
     * Update the authenticated user's mobile preferences.
     *
     * PATCH /api/v1/user/preferences
     */
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), self::VALIDATION_RULES);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'VALIDATION_ERROR',
                    'message' => 'Invalid preference values',
                    'details' => $validator->errors(),
                ],
            ], 422);
        }

        $allowedKeys = array_keys(self::DEFAULTS);
        $incoming = $request->only($allowedKeys);

        if (empty($incoming)) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'NO_VALID_FIELDS',
                    'message' => 'No valid preference fields provided',
                ],
            ], 400);
        }

        /** @var \App\Models\User $user */
        $user = $request->user();
        /** @var array<string, mixed> $stored */
        $stored = $user->mobile_preferences ?? [];
        $updated = array_merge($stored, $incoming);

        $user->mobile_preferences = $updated; /** @phpstan-ignore assign.propertyType */
        $user->save();

        $merged = array_merge(self::DEFAULTS, $updated);

        return response()->json([
            'success' => true,
            'data'    => $merged,
        ]);
    }
}

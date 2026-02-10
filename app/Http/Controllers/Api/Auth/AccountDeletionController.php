<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccountDeletionController extends Controller
{
    /**
     * Soft-delete the authenticated user's account and revoke all tokens.
     *
     * POST /auth/delete-account
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'confirmation' => ['required', 'string', 'in:DELETE'],
        ]);

        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'UNAUTHENTICATED',
                    'message' => 'No authenticated user.',
                ],
            ], 401);
        }

        Log::warning('Account deletion requested', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'ip'      => $request->ip(),
        ]);

        // Revoke all tokens
        $user->tokens()->delete();

        // Soft-delete the user
        $user->delete();

        return response()->json([
            'success' => true,
            'data'    => [
                'message'    => 'Account has been scheduled for deletion.',
                'deleted_at' => now()->toIso8601String(),
            ],
        ]);
    }
}

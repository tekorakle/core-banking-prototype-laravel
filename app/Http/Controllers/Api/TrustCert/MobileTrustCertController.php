<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\TrustCert;

use App\Domain\TrustCert\Enums\TrustLevel;
use App\Domain\TrustCert\Services\CertificateAuthorityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileTrustCertController extends Controller
{
    public function __construct(
        private readonly CertificateAuthorityService $certificateAuthority,
    ) {
    }

    /**
     * Get user's current trust certificate and trust level.
     *
     * GET /api/v1/trustcert/current
     */
    public function current(Request $request): JsonResponse
    {
        $user = $request->user();
        $subjectId = 'user:' . $user->id;

        $certificate = $this->certificateAuthority->getCertificateBySubject($subjectId);

        if (! $certificate) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'trust_level' => TrustLevel::UNKNOWN->value,
                    'label'       => TrustLevel::UNKNOWN->label(),
                    'certificate' => null,
                ],
            ]);
        }

        $trustLevel = TrustLevel::tryFrom($certificate->extensions['trust_level'] ?? 'unknown')
            ?? TrustLevel::UNKNOWN;

        return response()->json([
            'success' => true,
            'data'    => [
                'trust_level'   => $trustLevel->value,
                'label'         => $trustLevel->label(),
                'numeric_value' => $trustLevel->numericValue(),
                'certificate'   => $certificate->toArray(),
                'is_valid'      => $certificate->isValid(),
                'expires_at'    => $certificate->validUntil->format('c'),
            ],
        ]);
    }

    /**
     * Get all trust levels and their requirements.
     *
     * GET /api/v1/trustcert/requirements
     */
    public function requirements(): JsonResponse
    {
        $levels = [];
        foreach (TrustLevel::cases() as $level) {
            $levels[] = [
                'level'         => $level->value,
                'label'         => $level->label(),
                'numeric_value' => $level->numericValue(),
                'requirements'  => $level->requirements(),
                'limits'        => $this->getLimitsForLevel($level),
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $levels,
        ]);
    }

    /**
     * Get requirements for a specific trust level.
     *
     * GET /api/v1/trustcert/requirements/{level}
     */
    public function requirementsByLevel(string $level): JsonResponse
    {
        $trustLevel = TrustLevel::tryFrom($level);

        if (! $trustLevel) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'INVALID_TRUST_LEVEL',
                    'message' => 'Trust level not found.',
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'level'         => $trustLevel->value,
                'label'         => $trustLevel->label(),
                'numeric_value' => $trustLevel->numericValue(),
                'requirements'  => $trustLevel->requirements(),
                'limits'        => $this->getLimitsForLevel($trustLevel),
            ],
        ]);
    }

    /**
     * Get transaction limits for all trust levels.
     *
     * GET /api/v1/trustcert/limits
     */
    public function limits(): JsonResponse
    {
        $limits = [];
        foreach (TrustLevel::cases() as $level) {
            $limits[] = [
                'level'  => $level->value,
                'label'  => $level->label(),
                'limits' => $this->getLimitsForLevel($level),
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $limits,
        ]);
    }

    /**
     * Check if a transaction amount is within the user's trust level limits.
     *
     * POST /api/v1/trustcert/check-limit
     */
    public function checkLimit(Request $request): JsonResponse
    {
        $request->validate([
            'amount'           => ['required', 'numeric', 'min:0'],
            'transaction_type' => ['required', 'string', 'in:daily,monthly,single'],
        ]);

        $user = $request->user();
        $subjectId = 'user:' . $user->id;

        $certificate = $this->certificateAuthority->getCertificateBySubject($subjectId);
        $trustLevel = TrustLevel::UNKNOWN;

        if ($certificate && $certificate->isValid()) {
            $trustLevel = TrustLevel::tryFrom($certificate->extensions['trust_level'] ?? 'unknown')
                ?? TrustLevel::UNKNOWN;
        }

        $limits = $this->getLimitsForLevel($trustLevel);
        $type = $request->input('transaction_type');
        $amount = (float) $request->input('amount');
        $limit = $limits[$type] ?? 0;

        return response()->json([
            'success' => true,
            'data'    => [
                'allowed'     => $amount <= $limit,
                'trust_level' => $trustLevel->value,
                'limit'       => $limit,
                'amount'      => $amount,
                'type'        => $type,
                'remaining'   => max(0, $limit - $amount),
            ],
        ]);
    }

    /**
     * @return array<string, float>
     */
    private function getLimitsForLevel(TrustLevel $level): array
    {
        return match ($level) {
            TrustLevel::UNKNOWN  => ['daily' => 0, 'monthly' => 0, 'single' => 0],
            TrustLevel::BASIC    => ['daily' => 500, 'monthly' => 5000, 'single' => 200],
            TrustLevel::VERIFIED => ['daily' => 5000, 'monthly' => 50000, 'single' => 2000],
            TrustLevel::HIGH     => ['daily' => 50000, 'monthly' => 500000, 'single' => 25000],
            TrustLevel::ULTIMATE => ['daily' => 500000, 'monthly' => 5000000, 'single' => 250000],
        };
    }
}

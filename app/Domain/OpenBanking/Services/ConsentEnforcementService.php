<?php

declare(strict_types=1);

namespace App\Domain\OpenBanking\Services;

use App\Domain\OpenBanking\Enums\ConsentStatus;
use App\Domain\OpenBanking\Models\Consent;
use App\Domain\OpenBanking\Models\ConsentAccessLog;
use Carbon\Carbon;

final class ConsentEnforcementService
{
    /**
     * Validate that a TPP has valid consent to access the requested resource.
     */
    public function validateAccess(
        string $tppId,
        int $userId,
        string $permission,
        ?string $accountId = null,
    ): bool {
        $consent = Consent::where('tpp_id', $tppId)
            ->where('user_id', $userId)
            ->where('status', ConsentStatus::AUTHORIZED)
            ->first();

        if ($consent === null) {
            return false;
        }

        // Check expiry
        if ($consent->isExpired()) {
            $consent->update(['status' => ConsentStatus::EXPIRED]);

            return false;
        }

        // Check permission
        $permissions = $consent->permissions;
        if (! in_array($permission, $permissions, true)) {
            return false;
        }

        // Check account scope if specified
        if ($accountId !== null && $consent->account_ids !== null) {
            if (! in_array($accountId, $consent->account_ids, true)) {
                return false;
            }
        }

        // Check frequency limit
        if (! $this->checkFrequencyLimit($consent)) {
            return false;
        }

        return true;
    }

    /**
     * Log an access to the consent.
     */
    public function logAccess(
        string $consentId,
        string $tppId,
        string $endpoint,
        ?string $ipAddress = null,
    ): void {
        ConsentAccessLog::create([
            'consent_id' => $consentId,
            'tpp_id'     => $tppId,
            'endpoint'   => $endpoint,
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Check if the consent's daily access frequency limit has been reached.
     */
    public function checkFrequencyLimit(Consent $consent): bool
    {
        $todayCount = ConsentAccessLog::where('consent_id', $consent->id)
            ->whereDate('created_at', Carbon::today())
            ->count();

        return $todayCount < $consent->frequency_per_day;
    }
}

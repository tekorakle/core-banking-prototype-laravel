<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Infrastructure\Domain\DataObjects\DomainInfo;
use App\Infrastructure\Domain\DomainManager;
use App\Infrastructure\Domain\Enums\DomainStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Module Management API Controller.
 *
 * Provides REST endpoints for managing FinAegis domain modules:
 * listing, inspecting, enabling/disabling, and health verification.
 */
class ModuleController extends Controller
{
    public function __construct(
        private readonly DomainManager $domainManager,
    ) {
    }

    /**
     * List all modules with optional status filter.
     */
    public function index(Request $request): JsonResponse
    {
        $domains = $this->domainManager->getAvailableDomains();

        // Apply status filter if provided
        $statusFilter = $request->query('status');
        if (is_string($statusFilter) && $statusFilter !== '') {
            $status = DomainStatus::tryFrom($statusFilter);
            if ($status === null) {
                return response()->json([
                    'error' => [
                        'code'    => 'INVALID_FILTER',
                        'message' => 'Invalid status filter. Allowed values: ' . implode(', ', DomainStatus::values()),
                    ],
                ], 422);
            }

            $domains = $domains->filter(fn (DomainInfo $info) => $info->status === $status)->values();
        }

        // Apply type filter if provided
        $typeFilter = $request->query('type');
        if (is_string($typeFilter) && $typeFilter !== '') {
            $domains = $domains->filter(fn (DomainInfo $info) => $info->type->value === $typeFilter)->values();
        }

        $installed = $this->domainManager->getInstalledDomains();
        $disabled = $this->domainManager->getDisabledDomains();

        return response()->json([
            'data' => $domains->map(fn (DomainInfo $info) => $info->toArray()),
            'meta' => [
                'total'     => $domains->count(),
                'installed' => count($installed),
                'disabled'  => count($disabled),
                'statuses'  => DomainStatus::values(),
            ],
        ]);
    }

    /**
     * Get detailed information about a single module.
     */
    public function show(string $name): JsonResponse
    {
        $domains = $this->domainManager->getAvailableDomains();
        $normalizedName = $this->normalizeName($name);

        $domain = $domains->first(fn (DomainInfo $info) => $info->name === $normalizedName);

        if ($domain === null) {
            return response()->json([
                'error' => [
                    'code'    => 'MODULE_NOT_FOUND',
                    'message' => "Module '{$name}' not found",
                ],
            ], 404);
        }

        // Load manifest for detailed info
        $manifests = $this->domainManager->loadAllManifests();
        $manifest = $manifests[$normalizedName] ?? null;

        // Run verification for health status
        $verification = $this->domainManager->verify($name);

        return response()->json([
            'data' => [
                'module'       => $domain->toArray(),
                'manifest'     => $manifest?->toArray(),
                'verification' => $verification->toArray(),
            ],
        ]);
    }

    /**
     * Enable a disabled module.
     *
     * Requires admin privileges (enforced via route middleware).
     */
    public function enable(string $name): JsonResponse
    {
        $result = $this->domainManager->enable($name);

        if (! $result->success) {
            return response()->json([
                'error' => [
                    'code'    => 'ENABLE_FAILED',
                    'message' => $result->getSummary(),
                    'errors'  => $result->errors,
                ],
            ], 422);
        }

        return response()->json([
            'data'    => $result->toArray(),
            'message' => "Module '{$name}' enabled successfully",
        ]);
    }

    /**
     * Disable an active module.
     *
     * Requires admin privileges (enforced via route middleware).
     * Core modules cannot be disabled.
     */
    public function disable(string $name): JsonResponse
    {
        $result = $this->domainManager->disable($name);

        if (! $result->success) {
            return response()->json([
                'error' => [
                    'code'    => 'DISABLE_FAILED',
                    'message' => $result->getSummary(),
                    'errors'  => $result->errors,
                ],
            ], 422);
        }

        return response()->json([
            'data'    => $result->toArray(),
            'message' => "Module '{$name}' disabled successfully",
        ]);
    }

    /**
     * Get overall module health summary across all domains.
     */
    public function health(): JsonResponse
    {
        $domains = $this->domainManager->getAvailableDomains();
        $installed = $domains->filter(fn (DomainInfo $info) => $info->status === DomainStatus::INSTALLED);

        $results = [];
        $healthyCount = 0;
        $unhealthyCount = 0;

        foreach ($installed as $domain) {
            $verification = $this->domainManager->verify($domain->name);
            $results[] = [
                'name'    => $domain->name,
                'healthy' => $verification->valid,
                'passed'  => $verification->getPassedCount(),
                'failed'  => $verification->getFailedCount(),
                'errors'  => $verification->errors,
            ];

            if ($verification->valid) {
                $healthyCount++;
            } else {
                $unhealthyCount++;
            }
        }

        $overallHealthy = $unhealthyCount === 0;

        return response()->json([
            'data' => [
                'healthy' => $overallHealthy,
                'modules' => $results,
            ],
            'meta' => [
                'total_installed' => $installed->count(),
                'healthy'         => $healthyCount,
                'unhealthy'       => $unhealthyCount,
                'checked_at'      => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Verify health of a specific module.
     *
     * Requires admin privileges (enforced via route middleware).
     */
    public function verify(string $name): JsonResponse
    {
        $verification = $this->domainManager->verify($name);

        if (empty($verification->checks) && ! empty($verification->errors)) {
            return response()->json([
                'error' => [
                    'code'    => 'MODULE_NOT_FOUND',
                    'message' => "Module '{$name}' not found",
                    'errors'  => $verification->errors,
                ],
            ], 404);
        }

        $statusCode = $verification->valid ? 200 : 503;

        return response()->json([
            'data' => $verification->toArray(),
            'meta' => [
                'summary'    => $verification->getSummary(),
                'checked_at' => now()->toIso8601String(),
            ],
        ], $statusCode);
    }

    /**
     * Normalize a module name to the full package format.
     */
    private function normalizeName(string $name): string
    {
        if (! str_contains($name, '/')) {
            return "finaegis/{$name}";
        }

        return $name;
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Compliance\Services\Certification\BreachNotificationService;
use App\Domain\Compliance\Services\Certification\ConsentManagementService;
use App\Domain\Compliance\Services\Certification\DataProcessingRegisterService;
use App\Domain\Compliance\Services\Certification\DataRetentionService;
use App\Domain\Compliance\Services\Certification\DpiaService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class GdprEnhancedController extends Controller
{
    public function __construct(
        private readonly DataProcessingRegisterService $registerService,
        private readonly DpiaService $dpiaService,
        private readonly BreachNotificationService $breachService,
        private readonly ConsentManagementService $consentService,
        private readonly DataRetentionService $retentionService,
    ) {
    }

    // ── Article 30 Register ──────────────────────────────────────────────

    /**
     * Get processing activities register.
     */
    public function getRegister(Request $request): JsonResponse
    {
        try {
            $demoMode = config('compliance-certification.soc2.demo_mode', true);

            $data = $demoMode
                ? $this->registerService->getDemoRegister()
                : $this->registerService->exportRegister();

            return response()->json(['data' => $data]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to retrieve processing register',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a processing activity.
     */
    public function createActivity(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name'             => ['required', 'string'],
                'purpose'          => ['required', 'string'],
                'legal_basis'      => ['required', 'string'],
                'data_categories'  => ['nullable', 'array'],
                'data_subjects'    => ['nullable', 'array'],
                'recipients'       => ['nullable', 'array'],
                'retention_period' => ['nullable', 'string'],
            ]);

            $activity = $this->registerService->createActivity($validated);

            return response()->json([
                'message' => 'Processing activity created successfully',
                'data'    => $activity,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to create processing activity',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get register completeness check.
     */
    public function getRegisterCompleteness(): JsonResponse
    {
        try {
            $completeness = $this->registerService->checkCompleteness();

            return response()->json(['data' => $completeness]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to check register completeness',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ── DPIA ─────────────────────────────────────────────────────────────

    /**
     * Get DPIA summary.
     */
    public function getDpiaSummary(): JsonResponse
    {
        try {
            $demoMode = config('compliance-certification.soc2.demo_mode', true);

            $data = $demoMode
                ? $this->dpiaService->getDemoSummary()
                : $this->dpiaService->getSummary();

            return response()->json(['data' => $data]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to retrieve DPIA summary',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a DPIA.
     */
    public function createDpia(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title'                  => ['required', 'string'],
                'description'            => ['nullable', 'string'],
                'processing_activity_id' => ['nullable', 'string'],
                'risks'                  => ['nullable', 'array'],
                'mitigations'            => ['nullable', 'array'],
                'assessor'               => ['nullable', 'string'],
            ]);

            $assessment = $this->dpiaService->createAssessment($validated);

            return response()->json([
                'message' => 'DPIA created successfully',
                'data'    => $assessment,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to create DPIA',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Approve a DPIA.
     */
    public function approveDpia(Request $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reviewer' => ['required', 'string'],
            ]);

            $assessment = $this->dpiaService->approveAssessment($id, $validated['reviewer']);

            return response()->json([
                'message' => 'DPIA approved successfully',
                'data'    => $assessment,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to approve DPIA',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ── Breach Notification ──────────────────────────────────────────────

    /**
     * Get breach summary.
     */
    public function getBreachSummary(): JsonResponse
    {
        try {
            $demoMode = config('compliance-certification.soc2.demo_mode', true);

            $data = $demoMode
                ? $this->breachService->getDemoSummary()
                : $this->breachService->getSummary();

            return response()->json(['data' => $data]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to retrieve breach summary',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Report a data breach.
     */
    public function reportBreach(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title'                      => ['required', 'string'],
                'description'                => ['required', 'string'],
                'severity'                   => ['required', 'in:critical,high,medium,low'],
                'affected_data_types'        => ['nullable', 'array'],
                'affected_individuals_count' => ['nullable', 'integer'],
                'reported_by'                => ['nullable', 'string'],
            ]);

            $breach = $this->breachService->reportBreach($validated);

            return response()->json([
                'message' => 'Breach reported successfully — 72h notification deadline set',
                'data'    => $breach,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to report breach',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Notify authority about a breach.
     */
    public function notifyAuthority(Request $request, string $id): JsonResponse
    {
        try {
            $breach = $this->breachService->notifyAuthority($id, $request->input('notes'));

            return response()->json([
                'message' => 'Authority notification recorded',
                'data'    => $breach,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to record authority notification',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check breach deadlines.
     */
    public function checkDeadlines(): JsonResponse
    {
        try {
            $status = $this->breachService->checkDeadlines();

            return response()->json(['data' => $status]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to check deadlines',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ── Consent Management ───────────────────────────────────────────────

    /**
     * Get consent status for authenticated user.
     */
    public function getConsentStatus(Request $request): JsonResponse
    {
        try {
            $demoMode = config('compliance-certification.soc2.demo_mode', true);
            $userUuid = $request->user()?->uuid ?? 'demo-user';

            $data = $demoMode
                ? $this->consentService->getDemoStatus()
                : $this->consentService->getConsentStatus($userUuid);

            return response()->json(['data' => $data]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to retrieve consent status',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Record a consent decision.
     */
    public function recordConsent(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'purpose' => ['required', 'string'],
                'granted' => ['required', 'boolean'],
                'version' => ['sometimes', 'string'],
            ]);

            $userUuid = $request->user()?->uuid ?? 'demo-user';

            $record = $this->consentService->recordConsent(
                $userUuid,
                $validated['purpose'],
                $validated['granted'],
                [
                    'version'    => $validated['version'] ?? '1.0',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
            );

            return response()->json([
                'message' => 'Consent recorded successfully',
                'data'    => $record,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to record consent',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ── Data Retention ───────────────────────────────────────────────────

    /**
     * Get retention policy summary.
     */
    public function getRetentionSummary(): JsonResponse
    {
        try {
            $demoMode = config('compliance-certification.soc2.demo_mode', true);

            $data = $demoMode
                ? $this->retentionService->getDemoSummary()
                : $this->retentionService->getSummary();

            return response()->json(['data' => $data]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to retrieve retention summary',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a retention policy.
     */
    public function createRetentionPolicy(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'data_type'      => ['required', 'string'],
                'model_class'    => ['nullable', 'string'],
                'retention_days' => ['required', 'integer', 'min:1'],
                'action'         => ['sometimes', 'in:delete,archive,anonymize'],
                'description'    => ['nullable', 'string'],
            ]);

            $policy = $this->retentionService->createPolicy($validated);

            return response()->json([
                'message' => 'Retention policy created successfully',
                'data'    => $policy,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to create retention policy',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

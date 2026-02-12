<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Compliance\Services\Certification\AccessReviewService;
use App\Domain\Compliance\Services\Certification\DataClassificationService;
use App\Domain\Compliance\Services\Certification\DataResidencyService;
use App\Domain\Compliance\Services\Certification\EncryptionVerificationService;
use App\Domain\Compliance\Services\Certification\EvidenceCollectionService;
use App\Domain\Compliance\Services\Certification\GeoRoutingService;
use App\Domain\Compliance\Services\Certification\IncidentResponseService;
use App\Domain\Compliance\Services\Certification\KeyRotationService;
use App\Domain\Compliance\Services\Certification\NetworkSegmentationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ComplianceCertificationController extends Controller
{
    public function __construct(
        private readonly EvidenceCollectionService $evidenceService,
        private readonly AccessReviewService $accessReviewService,
        private readonly IncidentResponseService $incidentResponseService,
        private readonly DataClassificationService $classificationService,
        private readonly EncryptionVerificationService $encryptionVerificationService,
        private readonly KeyRotationService $keyRotationService,
        private readonly NetworkSegmentationService $networkSegmentationService,
        private readonly DataResidencyService $dataResidencyService,
        private readonly GeoRoutingService $geoRoutingService,
    ) {
    }

    /**
     * Get SOC 2 compliance evidence.
     */
    public function getEvidence(Request $request): JsonResponse
    {
        try {
            $evidence = $this->evidenceService->getEvidence(
                $request->query('period'),
                $request->query('type'),
            );

            return response()->json([
                'data' => $evidence,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to retrieve evidence',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Collect SOC 2 compliance evidence for a given period.
     */
    public function collectEvidence(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'period' => ['required', 'string'],
                'type'   => ['sometimes', 'string'],
            ]);

            $result = $this->evidenceService->collectEvidence(
                $validated['period'],
                $validated['type'] ?? 'all',
            );

            return response()->json([
                'message' => 'Evidence collection initiated successfully',
                'data'    => $result,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to collect evidence',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get access review report.
     */
    public function getAccessReview(): JsonResponse
    {
        try {
            $report = $this->accessReviewService->generateReviewReport();

            return response()->json([
                'data' => $report,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to generate access review report',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of privileged users.
     */
    public function getPrivilegedUsers(): JsonResponse
    {
        try {
            $users = $this->accessReviewService->getPrivilegedUsers();

            return response()->json([
                'data' => $users->toArray(),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to retrieve privileged users',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get incidents with optional status and severity filters.
     */
    public function getIncidents(Request $request): JsonResponse
    {
        try {
            $incidents = $this->incidentResponseService->getIncidents(
                $request->query('status'),
                $request->query('severity'),
            );

            return response()->json([
                'data' => $incidents,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to retrieve incidents',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new incident.
     */
    public function createIncident(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title'            => ['required', 'string'],
                'description'      => ['required', 'string'],
                'severity'         => ['required', 'in:critical,high,medium,low'],
                'affected_systems' => ['nullable', 'array'],
            ]);

            $incident = $this->incidentResponseService->createIncident($validated);

            return response()->json([
                'message' => 'Incident created successfully',
                'data'    => $incident,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to create incident',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing incident.
     */
    public function updateIncident(Request $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title'            => ['sometimes', 'string'],
                'description'      => ['sometimes', 'string'],
                'severity'         => ['sometimes', 'in:critical,high,medium,low'],
                'status'           => ['sometimes', 'string'],
                'affected_systems' => ['sometimes', 'array'],
            ]);

            $incident = $this->incidentResponseService->updateIncident($id, $validated);

            return response()->json([
                'message' => 'Incident updated successfully',
                'data'    => $incident,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to update incident',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resolve an incident.
     */
    public function resolveIncident(Request $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'resolution' => ['required', 'string'],
            ]);

            $incident = $this->incidentResponseService->resolveIncident($id, $validated['resolution']);

            return response()->json([
                'message' => 'Incident resolved successfully',
                'data'    => $incident,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to resolve incident',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get incident postmortem report.
     */
    public function getPostmortem(string $id): JsonResponse
    {
        try {
            $postmortem = $this->incidentResponseService->generatePostmortem($id);

            return response()->json([
                'data' => $postmortem,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to generate postmortem',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ── PCI DSS Endpoints ──────────────────────────────────────────────

    /**
     * Get data classification report.
     */
    public function getDataClassification(Request $request): JsonResponse
    {
        try {
            $demoMode = config('compliance-certification.soc2.demo_mode', true);

            $report = $demoMode
                ? $this->classificationService->getDemoReport()
                : $this->classificationService->generateComplianceReport();

            return response()->json([
                'data' => $report,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to retrieve data classification report',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run encryption verification suite.
     */
    public function getEncryptionVerification(): JsonResponse
    {
        try {
            $demoMode = config('compliance-certification.soc2.demo_mode', true);

            $results = $demoMode
                ? $this->encryptionVerificationService->getDemoResults()
                : $this->encryptionVerificationService->runVerification();

            return response()->json([
                'data' => $results,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to run encryption verification',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get key rotation status report.
     */
    public function getKeyRotationStatus(): JsonResponse
    {
        try {
            $demoMode = config('compliance-certification.soc2.demo_mode', true);

            $report = $demoMode
                ? $this->keyRotationService->getDemoReport()
                : $this->keyRotationService->generateRotationReport();

            return response()->json([
                'data' => $report,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to retrieve key rotation status',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Rotate a specific key (demo-safe).
     */
    public function rotateKey(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'key_identifier' => ['required', 'string'],
                'dry_run'        => ['sometimes', 'boolean'],
            ]);

            $result = $this->keyRotationService->rotateKey(
                $validated['key_identifier'],
                $validated['dry_run'] ?? false,
            );

            $status = $result['success'] ? 200 : 404;

            return response()->json([
                'data' => $result,
            ], $status);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to rotate key',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get network segmentation verification report.
     */
    public function getNetworkSegmentation(): JsonResponse
    {
        try {
            $demoMode = config('compliance-certification.soc2.demo_mode', true);

            $report = $demoMode
                ? $this->networkSegmentationService->getDemoReport()
                : $this->networkSegmentationService->verifySegmentation();

            return response()->json([
                'data' => $report,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to retrieve network segmentation report',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ── Data Residency Endpoints ─────────────────────────────────────────

    /**
     * Get data residency status for current tenant or specified region.
     */
    public function getResidencyStatus(Request $request): JsonResponse
    {
        try {
            $demoMode = config('compliance-certification.soc2.demo_mode', true);

            $status = $demoMode
                ? $this->dataResidencyService->getDemoStatus()
                : $this->dataResidencyService->getResidencyStatus();

            return response()->json([
                'data' => $status,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to retrieve data residency status',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get cross-region data transfer logs.
     */
    public function getTransferLogs(Request $request): JsonResponse
    {
        try {
            $logs = $this->dataResidencyService->getTransferLogs(
                $request->query('from_region'),
                $request->query('to_region'),
            );

            return response()->json([
                'data' => $logs,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to retrieve transfer logs',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Log a cross-region data transfer.
     */
    public function logTransfer(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'from_region' => ['required', 'string'],
                'to_region'   => ['required', 'string'],
                'data_type'   => ['required', 'string'],
                'reason'      => ['required', 'string'],
            ]);

            $log = $this->dataResidencyService->logTransfer(
                $validated['from_region'],
                $validated['to_region'],
                $validated['data_type'],
                $validated['reason'],
            );

            return response()->json([
                'message' => 'Transfer logged successfully',
                'data'    => $log,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to log transfer',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get geo-routing configuration and available regions.
     */
    public function getRoutingConfig(): JsonResponse
    {
        try {
            $config = $this->geoRoutingService->getRoutingConfig();

            return response()->json([
                'data' => $config,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'error'   => 'Failed to retrieve routing configuration',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

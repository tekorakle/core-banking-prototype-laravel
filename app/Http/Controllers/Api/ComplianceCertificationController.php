<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Compliance\Services\Certification\AccessReviewService;
use App\Domain\Compliance\Services\Certification\EvidenceCollectionService;
use App\Domain\Compliance\Services\Certification\IncidentResponseService;
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
}

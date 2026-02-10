<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\TrustCert;

use App\Domain\TrustCert\Enums\TrustLevel;
use App\Domain\TrustCert\Services\CertificateAuthorityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CertificateApplicationController extends Controller
{
    public function __construct(
        private readonly CertificateAuthorityService $certificateAuthority,
    ) {
    }

    /**
     * Create a new certificate application.
     *
     * POST /api/v1/trustcert/applications
     */
    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'target_level' => ['required', 'string', 'in:basic,verified,high,ultimate'],
        ]);

        $user = $request->user();
        $targetLevel = TrustLevel::from($request->input('target_level'));

        // Check for existing active application
        $existing = $this->getActiveApplication($user->id);
        if ($existing) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'APPLICATION_EXISTS',
                    'message' => 'An active application already exists.',
                ],
            ], 409);
        }

        $application = [
            'id'           => 'app_' . Str::random(20),
            'user_id'      => $user->id,
            'target_level' => $targetLevel->value,
            'status'       => 'draft',
            'requirements' => $targetLevel->requirements(),
            'documents'    => [],
            'created_at'   => now()->toIso8601String(),
            'updated_at'   => now()->toIso8601String(),
            'submitted_at' => null,
        ];

        $this->storeApplication($user->id, $application);

        return response()->json([
            'success' => true,
            'data'    => $application,
        ], 201);
    }

    /**
     * Get a specific application by ID.
     *
     * GET /api/v1/trustcert/applications/{id}
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $application = $this->findApplication($user->id, $id);

        if (! $application) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'APPLICATION_NOT_FOUND',
                    'message' => 'Application not found.',
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $application,
        ]);
    }

    /**
     * Get the user's current active application.
     *
     * GET /api/v1/trustcert/applications/current
     */
    public function currentApplication(Request $request): JsonResponse
    {
        $user = $request->user();
        $application = $this->getActiveApplication($user->id);

        return response()->json([
            'success' => true,
            'data'    => $application,
        ]);
    }

    /**
     * Upload documents for a certificate application.
     *
     * POST /api/v1/trustcert/applications/{id}/documents
     */
    public function uploadDocuments(string $id, Request $request): JsonResponse
    {
        $request->validate([
            'document_type' => ['required', 'string', 'in:identity,address,kyc,audit'],
            'file_name'     => ['required', 'string'],
        ]);

        $user = $request->user();
        $application = $this->findApplication($user->id, $id);

        if (! $application) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'APPLICATION_NOT_FOUND',
                    'message' => 'Application not found.',
                ],
            ], 404);
        }

        if ($application['status'] !== 'draft') {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'APPLICATION_NOT_EDITABLE',
                    'message' => 'Application is not in a draft state.',
                ],
            ], 422);
        }

        $document = [
            'id'            => 'doc_' . Str::random(16),
            'document_type' => $request->input('document_type'),
            'file_name'     => $request->input('file_name'),
            'uploaded_at'   => now()->toIso8601String(),
        ];

        $application['documents'][] = $document;
        $application['updated_at'] = now()->toIso8601String();
        $this->storeApplication($user->id, $application);

        return response()->json([
            'success' => true,
            'data'    => $document,
        ], 201);
    }

    /**
     * Submit an application for review.
     *
     * POST /api/v1/trustcert/applications/{id}/submit
     */
    public function submit(string $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $application = $this->findApplication($user->id, $id);

        if (! $application) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'APPLICATION_NOT_FOUND',
                    'message' => 'Application not found.',
                ],
            ], 404);
        }

        if ($application['status'] !== 'draft') {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'APPLICATION_NOT_SUBMITTABLE',
                    'message' => 'Only draft applications can be submitted.',
                ],
            ], 422);
        }

        $application['status'] = 'submitted';
        $application['submitted_at'] = now()->toIso8601String();
        $application['updated_at'] = now()->toIso8601String();
        $this->storeApplication($user->id, $application);

        return response()->json([
            'success' => true,
            'data'    => $application,
        ]);
    }

    /**
     * Cancel a pending application.
     *
     * POST /api/v1/trustcert/applications/{id}/cancel
     */
    public function cancel(string $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $application = $this->findApplication($user->id, $id);

        if (! $application) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'APPLICATION_NOT_FOUND',
                    'message' => 'Application not found.',
                ],
            ], 404);
        }

        if (in_array($application['status'], ['approved', 'cancelled'], true)) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'APPLICATION_NOT_CANCELLABLE',
                    'message' => 'This application cannot be cancelled.',
                ],
            ], 422);
        }

        $application['status'] = 'cancelled';
        $application['updated_at'] = now()->toIso8601String();
        $this->storeApplication($user->id, $application);

        return response()->json([
            'success' => true,
            'data'    => $application,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getActiveApplication(int $userId): ?array
    {
        $application = Cache::get("trustcert_application:{$userId}");

        if (! $application || in_array($application['status'], ['approved', 'cancelled'], true)) {
            return null;
        }

        return $application;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findApplication(int $userId, string $id): ?array
    {
        $application = Cache::get("trustcert_application:{$userId}");

        if (! $application || $application['id'] !== $id) {
            return null;
        }

        return $application;
    }

    /**
     * @param array<string, mixed> $application
     */
    private function storeApplication(int $userId, array $application): void
    {
        Cache::put("trustcert_application:{$userId}", $application, now()->addDays(30));
    }
}

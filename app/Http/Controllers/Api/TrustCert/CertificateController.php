<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\TrustCert;

use App\Domain\TrustCert\Services\CertificateExportService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CertificateController extends Controller
{
    public function __construct(
        private readonly CertificateExportService $exportService,
    ) {
    }

    /**
     * Get certificate details.
     *
     * GET /v1/trustcert/{certId}/certificate
     */
    public function show(string $certId): JsonResponse
    {
        $details = $this->exportService->getCertificateDetails($certId);

        if (! $details) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'CERTIFICATE_NOT_FOUND',
                    'message' => 'Certificate not found.',
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $details,
        ]);
    }

    /**
     * Export certificate as PDF.
     *
     * POST /v1/trustcert/{certId}/export-pdf
     */
    public function exportPdf(string $certId): JsonResponse
    {
        $result = $this->exportService->exportToPdf($certId);

        if (! $result) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'EXPORT_FAILED',
                    'message' => 'Certificate not found or PDF generation failed.',
                ],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }
}

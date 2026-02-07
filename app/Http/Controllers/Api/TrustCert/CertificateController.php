<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\TrustCert;

use App\Domain\TrustCert\Services\CertificateExportService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

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
    public function exportPdf(string $certId): Response|JsonResponse
    {
        $pdfContent = $this->exportService->exportToPdf($certId);

        if (! $pdfContent) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'EXPORT_FAILED',
                    'message' => 'Certificate not found or PDF generation failed.',
                ],
            ], 404);
        }

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"certificate-{$certId}.pdf\"",
        ]);
    }
}

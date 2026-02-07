<?php

declare(strict_types=1);

namespace App\Domain\TrustCert\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Service for exporting TrustCert certificates to PDF.
 *
 * Generates formatted PDF documents with certificate details,
 * trust chain info, and verification QR data.
 */
class CertificateExportService
{
    public function __construct(
        private readonly CertificateAuthorityService $caService,
        private readonly PresentationService $presentationService,
    ) {
    }

    /**
     * Get certificate details for API response.
     *
     * @return array<string, mixed>|null
     */
    public function getCertificateDetails(string $certificateId): ?array
    {
        $certificate = $this->caService->getCertificate($certificateId);

        if (! $certificate) {
            return null;
        }

        return [
            'certificateId' => $certificate->certificateId,
            'subjectId'     => $certificate->subjectId,
            'subject'       => $certificate->subject,
            'status'        => $certificate->status->value,
            'validFrom'     => $certificate->validFrom->format('c'),
            'validUntil'    => $certificate->validUntil->format('c'),
            'isValid'       => $certificate->isValid(),
            'isExpired'     => $certificate->isExpired(),
            'isRevoked'     => $certificate->isRevoked(),
            'isRoot'        => $certificate->isRootCertificate(),
            'fingerprint'   => $certificate->getFingerprint(),
            'extensions'    => $certificate->extensions,
            'disclaimer'    => 'This certificate is issued within the FinAegis ecosystem and is valid for identity verification purposes within this ecosystem only.',
        ];
    }

    /**
     * Export a certificate to PDF.
     *
     * @return string|null PDF content as string, or null if certificate not found
     */
    public function exportToPdf(string $certificateId): ?string
    {
        $certificate = $this->caService->getCertificate($certificateId);

        if (! $certificate) {
            return null;
        }

        try {
            // Generate a presentation token for the QR code
            $presentation = $this->presentationService->generatePresentation(
                certificateId: $certificateId,
                requestedClaims: ['certificate_type', 'status', 'valid_from', 'valid_until', 'subject_id'],
                validityMinutes: 60,
            );

            $data = [
                'certificate'     => $certificate,
                'verificationUrl' => $presentation['verification_url'] ?? null,
                'qrData'          => $presentation['qr_code_data'] ?? null,
                'deepLink'        => $presentation['deep_link'] ?? null,
                'generatedAt'     => now()->toIso8601String(),
                'disclaimer'      => 'This certificate is issued within the FinAegis ecosystem. It is intended for identity and compliance verification within FinAegis services only.',
                'branding'        => [
                    'name'   => 'FinAegis',
                    'shield' => 'Aegis Shield',
                ],
            ];

            /** @var \Barryvdh\DomPDF\PDF $pdf */
            $pdf = Pdf::loadView('trustcert.certificate-pdf', $data);
            $pdf->setPaper('a4', 'portrait');

            return $pdf->output();
        } catch (Throwable $e) {
            Log::error('Certificate PDF export failed', [
                'certificate_id' => $certificateId,
                'error'          => $e->getMessage(),
            ]);

            return null;
        }
    }
}

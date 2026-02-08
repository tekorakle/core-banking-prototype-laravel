<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\RegTech;

use App\Domain\RegTech\Enums\Jurisdiction;
use App\Domain\RegTech\Services\MicaComplianceService;
use App\Domain\RegTech\Services\MifidReportingService;
use App\Domain\RegTech\Services\RegTechOrchestrationService;
use App\Domain\RegTech\Services\TravelRuleService;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegTech\ApplicableRegulationsRequest;
use App\Http\Requests\RegTech\SubmitReportRequest;
use App\Http\Requests\RegTech\TravelRuleCheckRequest;
use App\Http\Requests\RegTech\WhitepaperValidationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RegTechController extends Controller
{
    public function __construct(
        private readonly RegTechOrchestrationService $orchestrationService,
        private readonly MifidReportingService $mifidService,
        private readonly MicaComplianceService $micaService,
        private readonly TravelRuleService $travelRuleService
    ) {
    }

    /**
     * Get RegTech compliance summary.
     *
     * GET /api/regtech/compliance/summary
     */
    public function complianceSummary(Request $request): JsonResponse
    {
        $jurisdiction = $request->query('jurisdiction');
        $jurisdictionEnum = $jurisdiction ? Jurisdiction::tryFrom((string) $jurisdiction) : null;

        $summary = $this->orchestrationService->getComplianceSummary($jurisdictionEnum);

        return response()->json([
            'success' => true,
            'data'    => $summary,
        ]);
    }

    /**
     * Submit a regulatory report.
     *
     * POST /api/regtech/reports
     */
    public function submitReport(SubmitReportRequest $request): JsonResponse
    {
        $validated = $request->validated();

        Log::info('RegTech: Report submission requested', [
            'user_id'      => $request->user()?->id,
            'ip_address'   => $request->ip(),
            'report_type'  => $validated['report_type'],
            'jurisdiction' => $validated['jurisdiction'],
        ]);

        $jurisdiction = Jurisdiction::tryFrom($validated['jurisdiction']);

        if (! $jurisdiction) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'error'   => [
                    'code'    => 'INVALID_JURISDICTION',
                    'message' => 'The provided jurisdiction is not supported.',
                ],
            ], 422);
        }

        $result = $this->orchestrationService->submitReport(
            $validated['report_type'],
            $jurisdiction,
            $validated['report_data'],
            $validated['metadata'] ?? []
        );

        $statusCode = $result['success'] ? 201 : 422;

        return response()->json([
            'success' => $result['success'],
            'data'    => $result['success'] ? [
                'reference' => $result['reference'],
                'details'   => $result['details'],
            ] : null,
            'error' => ! $result['success'] ? [
                'code'    => 'VALIDATION_FAILED',
                'message' => $result['errors'][0] ?? 'Report submission failed.',
                'details' => ['errors' => $result['errors']],
            ] : null,
        ], $statusCode);
    }

    /**
     * Check status of a submitted report.
     *
     * GET /api/regtech/reports/{reference}/status
     */
    public function reportStatus(string $reference, Request $request): JsonResponse
    {
        if (! preg_match('/^[A-Za-z0-9\-_]{1,100}$/', $reference)) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'INVALID_REFERENCE',
                    'message' => 'Invalid report reference format.',
                ],
            ], 422);
        }

        $jurisdiction = $request->query('jurisdiction', 'US');
        $jurisdictionEnum = Jurisdiction::tryFrom((string) $jurisdiction) ?? Jurisdiction::US;

        $result = $this->orchestrationService->checkReportStatus($reference, $jurisdictionEnum);

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }

    /**
     * Get registered adapters and their status.
     *
     * GET /api/regtech/adapters
     */
    public function adapters(): JsonResponse
    {
        $adapters = $this->orchestrationService->getAdapters();

        $data = [];
        foreach ($adapters as $key => $adapter) {
            $data[] = [
                'key'          => $key,
                'name'         => $adapter->getName(),
                'jurisdiction' => $adapter->getJurisdiction()->value,
                'report_types' => $adapter->getSupportedReportTypes(),
                'available'    => $adapter->isAvailable(),
                'sandbox'      => $adapter->isSandboxMode(),
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'adapters'  => $data,
                'total'     => count($data),
                'demo_mode' => $this->orchestrationService->isDemoMode(),
            ],
        ]);
    }

    /**
     * Get applicable regulations for a transaction.
     *
     * GET /api/regtech/regulations/applicable
     */
    public function applicableRegulations(ApplicableRegulationsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $amount = (float) $validated['amount'];
        $currency = $validated['currency'];
        $transactionType = $validated['transaction_type'];
        $isCrypto = (bool) ($validated['is_crypto'] ?? false);

        $regulations = $this->orchestrationService->getApplicableRegulations(
            $amount,
            $currency,
            $transactionType,
            ['is_crypto' => $isCrypto]
        );

        return response()->json([
            'success' => true,
            'data'    => [
                'regulations' => $regulations,
                'query'       => [
                    'amount'           => $amount,
                    'currency'         => $currency,
                    'transaction_type' => $transactionType,
                    'is_crypto'        => $isCrypto,
                ],
            ],
        ]);
    }

    /**
     * Get MiFID II reporting status and best execution analysis.
     *
     * GET /api/regtech/mifid/status
     */
    public function mifidStatus(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'enabled'                   => $this->mifidService->isEnabled(),
                'best_execution'            => $this->mifidService->getBestExecutionAnalysis(),
                'instrument_reference_data' => $this->mifidService->getInstrumentReferenceDataStatus(),
            ],
        ]);
    }

    /**
     * Get MiCA compliance status.
     *
     * GET /api/regtech/mica/status
     */
    public function micaStatus(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->micaService->getComplianceStatus(),
        ]);
    }

    /**
     * Validate a crypto-asset whitepaper.
     *
     * POST /api/regtech/mica/whitepaper/validate
     */
    public function validateWhitepaper(WhitepaperValidationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = $this->micaService->validateWhitepaper($validated);

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }

    /**
     * Get MiCA reserve management status.
     *
     * GET /api/regtech/mica/reserves
     */
    public function micaReserves(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->micaService->getReserveStatus(),
        ]);
    }

    /**
     * Check travel rule compliance for a transfer.
     *
     * POST /api/regtech/travel-rule/check
     */
    public function travelRuleCheck(TravelRuleCheckRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = $this->travelRuleService->evaluate($validated);

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }

    /**
     * Get travel rule thresholds for all jurisdictions.
     *
     * GET /api/regtech/travel-rule/thresholds
     */
    public function travelRuleThresholds(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->travelRuleService->getThresholds(),
        ]);
    }
}

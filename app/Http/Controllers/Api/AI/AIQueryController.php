<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\AI;

use App\Domain\AI\Services\NaturalLanguageProcessorService;
use App\Domain\AI\Services\TransactionQueryAnalyzerService;
use App\Http\Controllers\Controller;
use App\Http\Requests\AI\SpendingAnalysisRequest;
use App\Http\Requests\AI\TransactionQueryRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AIQueryController extends Controller
{
    public function __construct(
        private readonly NaturalLanguageProcessorService $nlpService,
        private readonly TransactionQueryAnalyzerService $analyzerService
    ) {
    }

    /**
     * Query transactions using natural language or structured filters.
     *
     * POST /api/ai/query/transactions
     */
    public function transactions(TransactionQueryRequest $request): JsonResponse
    {
        $validated = $request->validated();

        Log::info('AI Query: Transaction query', [
            'user_id' => $request->user()?->id,
            'query'   => $validated['query'] ?? null,
        ]);

        $filters = [];
        $parsed = null;

        // Parse natural language query
        if (! empty($validated['query'])) {
            $parsed = $this->nlpService->processQuery($validated['query'], [
                'user_id' => $request->user()?->id,
            ]);
            $filters = $this->analyzerService->buildFiltersFromEntities($parsed['entities'] ?? []);
        }

        // Merge explicit filters (override NL-parsed)
        foreach (['date_from', 'date_to', 'amount_min', 'amount_max', 'category', 'asset_code'] as $key) {
            if (isset($validated[$key])) {
                $filters[$key] = $validated[$key];
            }
        }

        $accountUuid = $validated['account_uuid'] ?? null;
        $queryResult = $this->analyzerService->executeQuery($filters, $accountUuid);

        $nlSummary = $this->analyzerService->generateNaturalLanguageSummary(
            $queryResult,
            $validated['query'] ?? 'transaction query'
        );

        return response()->json([
            'success' => true,
            'data'    => [
                'transactions' => $queryResult['transactions'],
                'summary'      => $queryResult['summary'],
                'total_count'  => $queryResult['total_count'],
                'nl_summary'   => $nlSummary,
                'query_parsed' => $parsed ? [
                    'intent'      => $parsed['intent'] ?? null,
                    'confidence'  => $parsed['confidence'] ?? null,
                    'explanation' => $parsed['explanation'] ?? null,
                ] : null,
                'filters_used' => $queryResult['filters'],
            ],
        ]);
    }

    /**
     * Analyze spending patterns by category, merchant, and time.
     *
     * POST /api/ai/query/spending-analysis
     */
    public function spendingAnalysis(SpendingAnalysisRequest $request): JsonResponse
    {
        $validated = $request->validated();

        Log::info('AI Query: Spending analysis', [
            'user_id' => $request->user()?->id,
            'query'   => $validated['query'] ?? null,
        ]);

        $filters = [];

        if (! empty($validated['query'])) {
            $parsed = $this->nlpService->processQuery($validated['query'], []);
            $filters = $this->analyzerService->buildFiltersFromEntities($parsed['entities'] ?? []);
        }

        foreach (['date_from', 'date_to', 'category', 'asset_code'] as $key) {
            if (isset($validated[$key])) {
                $filters[$key] = $validated[$key];
            }
        }

        $accountUuid = $validated['account_uuid'] ?? null;
        $analysis = $this->analyzerService->analyzeSpending($filters, $accountUuid);

        return response()->json([
            'success' => true,
            'data'    => $analysis,
        ]);
    }
}

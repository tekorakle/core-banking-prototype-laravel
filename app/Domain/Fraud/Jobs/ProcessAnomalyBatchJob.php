<?php

declare(strict_types=1);

namespace App\Domain\Fraud\Jobs;

use App\Domain\Account\Models\Transaction;
use App\Domain\Fraud\Services\AnomalyDetectionOrchestrator;
use DateTimeInterface;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessAnomalyBatchJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 600;

    /**
     * @param  array<int>  $transactionIds  The transaction IDs to scan
     */
    public function __construct(
        private readonly array $transactionIds,
        private readonly string $pipelineRunId,
    ) {
        $this->onQueue('fraud-batch');
    }

    public function handle(AnomalyDetectionOrchestrator $orchestrator): void
    {
        $transactions = Transaction::with('account.user')
            ->whereIn('id', $this->transactionIds)
            ->get();

        $processed = 0;
        $anomaliesFound = 0;

        foreach ($transactions as $transaction) {
            try {
                $account = $transaction->account;
                $user = $account?->user;

                $eventProps = is_array($transaction->event_properties) ? $transaction->event_properties : [];
                $metaData = is_array($transaction->meta_data) ? $transaction->meta_data : [];

                $context = [
                    'amount'          => (float) ($eventProps['amount'] ?? 0),
                    'type'            => $metaData['type'] ?? 'unknown',
                    'pipeline_run_id' => $this->pipelineRunId,
                ];

                if ($transaction->created_at) {
                    $createdAt = $transaction->created_at instanceof DateTimeInterface
                        ? $transaction->created_at
                        : \Carbon\Carbon::parse($transaction->created_at);
                    $context['hour_of_day'] = $createdAt->hour;
                    $context['day_of_week'] = $createdAt->dayOfWeek;
                }

                $result = $orchestrator->detectAnomalies(
                    $context,
                    (string) $transaction->id,
                    Transaction::class,
                    $user?->id,
                );

                $processed++;

                if ($result['highest_score'] > 0) {
                    $anomaliesFound++;
                }
            } catch (Exception $e) {
                Log::warning('Batch anomaly scan failed for transaction', [
                    'transaction_id'  => $transaction->id,
                    'pipeline_run_id' => $this->pipelineRunId,
                    'error'           => $e->getMessage(),
                ]);
            }
        }

        Log::info('Anomaly batch chunk completed', [
            'pipeline_run_id' => $this->pipelineRunId,
            'processed'       => $processed,
            'anomalies_found' => $anomaliesFound,
            'total_in_chunk'  => count($this->transactionIds),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Anomaly batch job failed', [
            'pipeline_run_id'   => $this->pipelineRunId,
            'transaction_count' => count($this->transactionIds),
            'error'             => $exception->getMessage(),
        ]);
    }
}

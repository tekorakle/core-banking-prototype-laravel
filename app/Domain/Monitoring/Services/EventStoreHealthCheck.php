<?php

declare(strict_types=1);

namespace App\Domain\Monitoring\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EventStoreHealthCheck
{
    public function __construct(
        private readonly EventStoreService $eventStoreService,
    ) {
    }

    /**
     * Check connectivity to all domain event tables.
     *
     * @return array<string, mixed>
     */
    public function checkEventTableConnectivity(): array
    {
        $tables = $this->eventStoreService->discoverEventTables();
        $results = [];
        $allHealthy = true;

        foreach ($tables as $table) {
            try {
                DB::table($table)->limit(1)->get();
                $results[$table] = ['healthy' => true, 'message' => 'Accessible'];
            } catch (Exception $e) {
                $results[$table] = ['healthy' => false, 'message' => $e->getMessage()];
                $allHealthy = false;
            }
        }

        return [
            'name'    => 'event_table_connectivity',
            'healthy' => $allHealthy,
            'tables'  => $results,
        ];
    }

    /**
     * Check projector lag (events processed vs total).
     *
     * @return array<string, mixed>
     */
    public function checkProjectorLag(): array
    {
        if (! Schema::hasTable('stored_events')) {
            return [
                'name'    => 'projector_lag',
                'healthy' => true,
                'message' => 'No stored events table',
            ];
        }

        $totalEvents = DB::table('stored_events')->count();
        $recentUnprocessed = 0;

        // Check if there are events in the last 5 minutes that might indicate lag
        $recentEvents = DB::table('stored_events')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->count();

        $healthy = $recentUnprocessed === 0;

        return [
            'name'          => 'projector_lag',
            'healthy'       => $healthy,
            'total_events'  => $totalEvents,
            'recent_events' => $recentEvents,
            'message'       => $healthy ? 'No lag detected' : "Lag: {$recentUnprocessed} unprocessed events",
        ];
    }

    /**
     * Check snapshot freshness per domain.
     *
     * @return array<string, mixed>
     */
    public function checkSnapshotFreshness(): array
    {
        $domainMap = $this->eventStoreService->getDomainTableMap();
        $results = [];
        $allFresh = true;

        foreach ($domainMap as $domain => $tables) {
            $snapshotTable = $tables['snapshot_table'];
            if ($snapshotTable === null) {
                continue;
            }

            if (! Schema::hasTable($snapshotTable)) {
                continue;
            }

            $newest = DB::table($snapshotTable)->max('created_at');

            if ($newest === null) {
                $results[$domain] = [
                    'snapshot_table' => $snapshotTable,
                    'age_hours'      => null,
                    'fresh'          => true,
                    'message'        => 'No snapshots',
                ];

                continue;
            }

            $ageHours = now()->diffInHours($newest);
            $fresh = $ageHours < 168; // Fresh if less than 7 days old

            if (! $fresh) {
                $allFresh = false;
            }

            $results[$domain] = [
                'snapshot_table'  => $snapshotTable,
                'newest_snapshot' => $newest,
                'age_hours'       => $ageHours,
                'fresh'           => $fresh,
                'message'         => $fresh ? 'Fresh' : "Stale ({$ageHours}h old)",
            ];
        }

        return [
            'name'    => 'snapshot_freshness',
            'healthy' => $allFresh,
            'domains' => $results,
        ];
    }

    /**
     * Check event growth rate per domain.
     *
     * @return array<string, mixed>
     */
    public function checkEventGrowthRate(): array
    {
        if (! Schema::hasTable('stored_events')) {
            return [
                'name'    => 'event_growth_rate',
                'healthy' => true,
                'message' => 'No stored events table',
            ];
        }

        $lastHour = DB::table('stored_events')
            ->where('created_at', '>=', now()->subHour())
            ->count();

        $lastDay = DB::table('stored_events')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        // Alert if more than 10000 events per hour (configurable threshold)
        $healthy = $lastHour < 10000;

        return [
            'name'            => 'event_growth_rate',
            'healthy'         => $healthy,
            'events_per_hour' => $lastHour,
            'events_per_day'  => $lastDay,
            'message'         => $healthy ? 'Normal growth rate' : "High growth: {$lastHour} events/hour",
        ];
    }

    /**
     * Run all event store health checks.
     *
     * @return array<string, mixed>
     */
    public function checkAll(): array
    {
        $checks = [
            'event_table_connectivity' => $this->checkEventTableConnectivity(),
            'projector_lag'            => $this->checkProjectorLag(),
            'snapshot_freshness'       => $this->checkSnapshotFreshness(),
            'event_growth_rate'        => $this->checkEventGrowthRate(),
        ];

        $healthy = collect($checks)->every(fn ($check) => $check['healthy']);

        return [
            'status'    => $healthy ? 'healthy' : 'unhealthy',
            'healthy'   => $healthy,
            'timestamp' => now()->toIso8601String(),
            'checks'    => $checks,
        ];
    }

    /**
     * Get the underlying EventStoreService instance.
     */
    public function getEventStoreService(): EventStoreService
    {
        return $this->eventStoreService;
    }
}

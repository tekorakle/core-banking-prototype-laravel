<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Monitoring\Services\EventStoreService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\EventSourcing\Facades\Projectionist;

class EventReplayCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:replay
                            {--domain= : Replay events for a specific domain}
                            {--projector= : Replay through a specific projector class}
                            {--from= : Replay events from this date (Y-m-d)}
                            {--to= : Replay events up to this date (Y-m-d)}
                            {--dry-run : Show what would be replayed without executing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Replay stored events through projectors to rebuild read models';

    /**
     * Execute the console command.
     */
    public function handle(EventStoreService $eventStoreService): int
    {
        $domain = $this->option('domain');
        $projectorClass = $this->option('projector');
        $from = $this->option('from');
        $to = $this->option('to');
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN - No events will be replayed.');
        }

        // Validate domain if provided
        if ($domain) {
            $eventTable = $eventStoreService->resolveEventTable($domain);
            if ($eventTable === null) {
                $this->error("Unknown domain: {$domain}");

                return Command::FAILURE;
            }

            $eventCount = $eventStoreService->countEvents($eventTable, $from, $to);
            $this->info("Domain: {$domain}");
            $this->info("Event table: {$eventTable}");
            $this->info("Events to replay: {$eventCount}");
        } else {
            $allStats = $eventStoreService->getAllStats();
            $totalEvents = $allStats['summary']['total_events'] ?? 0;
            $this->info("Replaying all events ({$totalEvents} total)");
        }

        if ($projectorClass) {
            $this->info("Projector filter: {$projectorClass}");
        }

        if ($from) {
            $this->info("From: {$from}");
        }
        if ($to) {
            $this->info("To: {$to}");
        }

        if ($dryRun) {
            $this->info('Dry run complete. No events were replayed.');

            return Command::SUCCESS;
        }

        if (! $this->confirm('This will replay events through projectors. Continue?')) {
            $this->info('Replay cancelled.');

            return Command::SUCCESS;
        }

        $this->info('Starting event replay...');

        DB::transaction(function () use ($projectorClass) {
            if ($projectorClass && class_exists($projectorClass)) {
                Projectionist::replay(collect([app($projectorClass)]));
            } else {
                Projectionist::replay(Projectionist::getProjectors());
            }
        });

        $this->info('Event replay completed successfully.');

        return Command::SUCCESS;
    }
}

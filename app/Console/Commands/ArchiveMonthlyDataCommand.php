<?php

namespace App\Console\Commands;

use App\Jobs\ArchiveMonthlyDataJob;
use App\Services\Archiving\MonthlyHistoriqueArchiver;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class ArchiveMonthlyDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'archive:monthly
        {--cutoff-months=12 : Archive records older than this many months}
        {--batch=2000 : Number of rows per batch}
        {--queue : Dispatch the archiving work to queue}
        {--dry-run : Print candidate counts without moving data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive old historiques rows to archive schema with integrity checks.';

    public function handle(MonthlyHistoriqueArchiver $archiver): int
    {
        $cutoffMonths = max((int) $this->option('cutoff-months'), 1);
        $batchSize = max((int) $this->option('batch'), 100);
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = CarbonImmutable::now()->subMonths($cutoffMonths)->startOfMonth();

        if ((bool) $this->option('queue')) {
            ArchiveMonthlyDataJob::dispatch(
                cutoffIso: $cutoff->toIso8601String(),
                batchSize: $batchSize,
                dryRun: $dryRun,
            );

            $this->info("Archive job queued (cutoff={$cutoff->toDateString()}, batch={$batchSize}, dry-run=".($dryRun ? 'yes' : 'no').').');

            return self::SUCCESS;
        }

        $result = $archiver->archive(
            cutoff: $cutoff,
            batchSize: $batchSize,
            dryRun: $dryRun,
        );

        $this->table(
            ['Key', 'Value'],
            [
                ['dry_run', $result['dry_run'] ? 'true' : 'false'],
                ['cutoff', (string) $result['cutoff']],
                ['batch_size', (string) $result['batch_size']],
                ['candidates', (string) $result['candidates']],
                ['archived', (string) $result['archived']],
                ['deleted', (string) $result['deleted']],
                ['batches', (string) $result['batches']],
            ]
        );

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Jobs\ArchiveMonthlyFinancialDataJob;
use App\Services\Archiving\MonthlyFinancialArchiver;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class ArchiveMonthlyFinancialDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'archive:financial-monthly
        {--cutoff-months=18 : Archive closed financial records older than this many months}
        {--batch=1000 : Number of parent rows per batch}
        {--queue : Dispatch the archiving work to queue}
        {--dry-run : Print candidate counts without moving data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive closed ventes/commandes and their details/paiements to archive schema.';

    public function handle(MonthlyFinancialArchiver $archiver): int
    {
        $cutoffMonths = max((int) $this->option('cutoff-months'), 1);
        $batchSize = max((int) $this->option('batch'), 100);
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = CarbonImmutable::now()->subMonths($cutoffMonths)->startOfMonth();

        if ((bool) $this->option('queue')) {
            ArchiveMonthlyFinancialDataJob::dispatch(
                cutoffIso: $cutoff->toIso8601String(),
                batchSize: $batchSize,
                dryRun: $dryRun,
            );

            $this->info("Financial archive job queued (cutoff={$cutoff->toDateString()}, batch={$batchSize}, dry-run=".($dryRun ? 'yes' : 'no').').');

            return self::SUCCESS;
        }

        $result = $archiver->archive(
            cutoff: $cutoff,
            batchSize: $batchSize,
            dryRun: $dryRun,
        );

        $this->table(
            ['Metric', 'Value'],
            [
                ['dry_run', $result['dry_run'] ? 'true' : 'false'],
                ['cutoff', (string) $result['cutoff']],
                ['batch_size', (string) $result['batch_size']],
                ['candidates.ventes', (string) $result['candidates']['ventes']],
                ['candidates.commandes', (string) $result['candidates']['commandes']],
                ['archived.ventes', (string) $result['archived']['ventes']],
                ['archived.vente_details', (string) $result['archived']['vente_details']],
                ['archived.commandes', (string) $result['archived']['commandes']],
                ['archived.commande_details', (string) $result['archived']['commande_details']],
                ['archived.paiements', (string) $result['archived']['paiements']],
                ['deleted.ventes', (string) $result['deleted']['ventes']],
                ['deleted.vente_details', (string) $result['deleted']['vente_details']],
                ['deleted.commandes', (string) $result['deleted']['commandes']],
                ['deleted.commande_details', (string) $result['deleted']['commande_details']],
                ['deleted.paiements', (string) $result['deleted']['paiements']],
                ['batches.ventes', (string) $result['batches']['ventes']],
                ['batches.commandes', (string) $result['batches']['commandes']],
            ]
        );

        return self::SUCCESS;
    }
}

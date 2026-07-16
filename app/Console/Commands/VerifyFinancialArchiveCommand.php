<?php

namespace App\Console\Commands;

use App\Services\Archiving\FinancialArchiveVerifier;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Throwable;

class VerifyFinancialArchiveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'archive:verify-financial
        {--cutoff-months=18 : Verify records older than this many months}
        {--show-monthly : Print month-level parity tables}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify archive integrity and detect drift between hot and archive financial tables.';

    public function handle(FinancialArchiveVerifier $verifier): int
    {
        $cutoffMonths = max((int) $this->option('cutoff-months'), 1);
        $cutoff = CarbonImmutable::now()->subMonths($cutoffMonths)->startOfMonth();

        try {
            $report = $verifier->verify($cutoff);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            $this->line('Run migrations then retry: php artisan migrate && php artisan archive:verify-financial');

            return self::FAILURE;
        }

        $summary = $report['summary'];

        $this->info('Financial archive verification report');
        $this->line('Cutoff: '.$report['cutoff']);

        $this->table(
            ['Metric', 'Value'],
            [
                ['hot_eligible.ventes', (string) $summary['hot_eligible']['ventes']],
                ['hot_eligible.commandes', (string) $summary['hot_eligible']['commandes']],
                ['hot_eligible.paiements', (string) $summary['hot_eligible']['paiements']],
                ['archive_totals.ventes', (string) $summary['archive_totals']['ventes']],
                ['archive_totals.vente_details', (string) $summary['archive_totals']['vente_details']],
                ['archive_totals.commandes', (string) $summary['archive_totals']['commandes']],
                ['archive_totals.commande_details', (string) $summary['archive_totals']['commande_details']],
                ['archive_totals.paiements', (string) $summary['archive_totals']['paiements']],
                ['overlap.ventes', (string) $summary['overlap']['ventes']],
                ['overlap.commandes', (string) $summary['overlap']['commandes']],
                ['overlap.paiements', (string) $summary['overlap']['paiements']],
                ['orphan_archive_rows.vente_details_without_parent', (string) $summary['orphan_archive_rows']['vente_details_without_parent']],
                ['orphan_archive_rows.commande_details_without_parent', (string) $summary['orphan_archive_rows']['commande_details_without_parent']],
                ['orphan_archive_rows.paiements_without_parent', (string) $summary['orphan_archive_rows']['paiements_without_parent']],
            ]
        );

        if ((bool) $this->option('show-monthly')) {
            foreach (['ventes', 'commandes', 'paiements'] as $tableName) {
                $this->newLine();
                $this->info("Monthly parity: {$tableName}");

                $rows = array_map(
                    static fn (array $row): array => [
                        $row['month'],
                        (string) $row['hot_eligible'],
                        (string) $row['archive_count'],
                    ],
                    $report['monthly'][$tableName]
                );

                $this->table(['Month', 'Hot Eligible', 'Archive Count'], $rows);
            }
        }

        $hasDrift = $summary['hot_eligible']['ventes'] > 0
            || $summary['hot_eligible']['commandes'] > 0
            || $summary['overlap']['ventes'] > 0
            || $summary['overlap']['commandes'] > 0
            || $summary['orphan_archive_rows']['vente_details_without_parent'] > 0
            || $summary['orphan_archive_rows']['commande_details_without_parent'] > 0
            || $summary['orphan_archive_rows']['paiements_without_parent'] > 0;

        return $hasDrift ? self::FAILURE : self::SUCCESS;
    }
}

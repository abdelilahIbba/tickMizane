<?php

namespace App\Services\Archiving;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FinancialArchiveVerifier
{
    /**
     * @return array<string, mixed>
     */
    public function verify(DateTimeInterface $cutoff): array
    {
        $cutoffAt = CarbonImmutable::instance($cutoff);
        $cutoffSql = $cutoffAt->toDateTimeString();

        if (! $this->archiveTablesReady()) {
            throw new RuntimeException('Archive financial tables are missing. Run migrations first (php artisan migrate).');
        }

        $summary = [
            'hot_eligible' => [
                'ventes' => $this->hotEligibleVentes($cutoffSql),
                'commandes' => $this->hotEligibleCommandes($cutoffSql),
                'paiements' => $this->hotEligiblePaiements($cutoffSql),
            ],
            'archive_totals' => [
                'ventes' => (int) DB::table('archive.ventes')->count(),
                'vente_details' => (int) DB::table('archive.vente_details')->count(),
                'commandes' => (int) DB::table('archive.commandes')->count(),
                'commande_details' => (int) DB::table('archive.commande_details')->count(),
                'paiements' => (int) DB::table('archive.paiements')->count(),
            ],
            'overlap' => [
                'ventes' => (int) DB::scalar("SELECT COUNT(*) FROM ventes v INNER JOIN archive.ventes av ON av.id = v.id WHERE v.created_at < ? AND v.status IN ('paid','cancelled')", [$cutoffSql]),
                'commandes' => (int) DB::scalar("SELECT COUNT(*) FROM commandes c INNER JOIN archive.commandes ac ON ac.id = c.id WHERE c.created_at < ? AND ((c.type = 'kitchen' AND c.status IN ('payee','annule')) OR (c.type = 'supplier' AND c.status IN ('received','annule')))", [$cutoffSql]),
                'paiements' => (int) DB::scalar("SELECT COUNT(*) FROM paiements p INNER JOIN archive.paiements ap ON ap.id = p.id WHERE p.created_at < ?", [$cutoffSql]),
            ],
            'orphan_archive_rows' => [
                'vente_details_without_parent' => (int) DB::scalar('SELECT COUNT(*) FROM archive.vente_details vd LEFT JOIN archive.ventes v ON v.id = vd.vente_id WHERE v.id IS NULL'),
                'commande_details_without_parent' => (int) DB::scalar('SELECT COUNT(*) FROM archive.commande_details cd LEFT JOIN archive.commandes c ON c.id = cd.commande_id WHERE c.id IS NULL'),
                'paiements_without_parent' => (int) DB::scalar("SELECT COUNT(*) FROM archive.paiements p LEFT JOIN archive.ventes v ON v.id = p.vente_id LEFT JOIN archive.commandes c ON c.id = p.commande_id WHERE (p.vente_id IS NOT NULL AND v.id IS NULL) OR (p.commande_id IS NOT NULL AND c.id IS NULL)"),
            ],
        ];

        return [
            'cutoff' => $cutoffAt->toDateTimeString(),
            'summary' => $summary,
            'monthly' => [
                'ventes' => $this->monthlyParityRows(
                    archiveTable: 'archive.ventes',
                    hotTable: 'ventes',
                    hotWhereSql: "created_at < '{$cutoffSql}' AND status IN ('paid','cancelled')"
                ),
                'commandes' => $this->monthlyParityRows(
                    archiveTable: 'archive.commandes',
                    hotTable: 'commandes',
                    hotWhereSql: "created_at < '{$cutoffSql}' AND ((type = 'kitchen' AND status IN ('payee','annule')) OR (type = 'supplier' AND status IN ('received','annule')))"
                ),
                'paiements' => $this->monthlyParityRows(
                    archiveTable: 'archive.paiements',
                    hotTable: 'paiements',
                    hotWhereSql: "created_at < '{$cutoffSql}'"
                ),
            ],
        ];
    }

    /**
     * @return array<int, array{month:string,hot_eligible:int,archive_count:int}>
     */
    private function monthlyParityRows(string $archiveTable, string $hotTable, string $hotWhereSql): array
    {
        $sql = "
            WITH archive_months AS (
                SELECT to_char(date_trunc('month', created_at), 'YYYY-MM') AS month_key,
                       COUNT(*) AS archive_count
                FROM {$archiveTable}
                GROUP BY 1
            ),
            hot_months AS (
                SELECT to_char(date_trunc('month', created_at), 'YYYY-MM') AS month_key,
                       COUNT(*) AS hot_eligible
                FROM {$hotTable}
                WHERE {$hotWhereSql}
                GROUP BY 1
            )
            SELECT COALESCE(a.month_key, h.month_key) AS month,
                   COALESCE(h.hot_eligible, 0) AS hot_eligible,
                   COALESCE(a.archive_count, 0) AS archive_count
            FROM archive_months a
            FULL OUTER JOIN hot_months h ON h.month_key = a.month_key
            ORDER BY month
        ";

        return array_map(
            static fn ($row) => [
                'month' => (string) $row->month,
                'hot_eligible' => (int) $row->hot_eligible,
                'archive_count' => (int) $row->archive_count,
            ],
            DB::select($sql)
        );
    }

    private function hotEligibleVentes(string $cutoffSql): int
    {
        return (int) DB::scalar("SELECT COUNT(*) FROM ventes WHERE created_at < ? AND status IN ('paid','cancelled')", [$cutoffSql]);
    }

    private function hotEligibleCommandes(string $cutoffSql): int
    {
        return (int) DB::scalar(
            "SELECT COUNT(*) FROM commandes WHERE created_at < ? AND ((type = 'kitchen' AND status IN ('payee','annule')) OR (type = 'supplier' AND status IN ('received','annule')))",
            [$cutoffSql]
        );
    }

    private function hotEligiblePaiements(string $cutoffSql): int
    {
        return (int) DB::scalar('SELECT COUNT(*) FROM paiements WHERE created_at < ?', [$cutoffSql]);
    }

    private function archiveTablesReady(): bool
    {
        $required = [
            'ventes',
            'vente_details',
            'commandes',
            'commande_details',
            'paiements',
        ];

        $count = (int) DB::table('information_schema.tables')
            ->where('table_schema', 'archive')
            ->whereIn('table_name', $required)
            ->count();

        return $count === count($required);
    }
}

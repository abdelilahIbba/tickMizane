<?php

namespace App\Services\Archiving;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * MonthlyFinancialArchiver - أرشفة البيانات المالية الشهرية
 *
 * مسؤول عن نقل السجلات المالية القديمة والمغلقة
 * من الجداول الرئيسية (ventes, commandes, paiements)
 * إلى مخطط الأرشيف (archive.*) بطريقة آمنة.
 *
 * الميزات:
 * - معالجة الدفعات (batch) لتجنب إشباع الذاكرة
 * - التحقق من نجاح النقل قبل الحذف (Verify before delete)
 * - وضع المحاكاة (dry_run) لاستعراض ما سيُحذف
 * - استخدام المعاملات (DB::transaction) لضمان سلامة البيانات
 *
 * يعمل ضمن ArchiveMonthlyFinancialDataJob المجدول شهرياً
 */
class MonthlyFinancialArchiver
{
    private const VENTE_CLOSED = ['paid', 'cancelled'];

    private const KITCHEN_COMMANDE_CLOSED = ['payee', 'annule'];

    private const SUPPLIER_COMMANDE_CLOSED = ['received', 'annule'];

    /**
     * @var string[]
     */
    private const VENTE_COLUMNS = [
        'id',
        'user_id',
        'table_id',
        'total',
        'payment_method',
        'status',
        'created_at',
        'updated_at',
    ];

    /**
     * @var string[]
     */
    private const VENTE_DETAIL_COLUMNS = [
        'id',
        'vente_id',
        'produit_id',
        'quantity',
        'price',
        'total_line',
        'created_at',
        'updated_at',
    ];

    /**
     * @var string[]
     */
    private const COMMANDE_COLUMNS = [
        'id',
        'fournisseur_id',
        'user_id',
        'table_id',
        'total',
        'status',
        'type',
        'waiter_notes',
        'created_at',
        'updated_at',
        'ready_at',
        'validated_at',
    ];

    /**
     * @var string[]
     */
    private const COMMANDE_DETAIL_COLUMNS = [
        'id',
        'commande_id',
        'produit_id',
        'quantity',
        'price',
        'notes',
        'created_at',
        'updated_at',
    ];

    /**
     * @var string[]
     */
    private const PAIEMENT_COLUMNS = [
        'id',
        'vente_id',
        'commande_id',
        'amount',
        'method',
        'reference',
        'user_id',
        'status',
        'notes',
        'created_at',
        'updated_at',
    ];

    /**
     * Archive closed financial records older than cutoff.
     *
     * @return array<string, int|string|bool|array<string, int>>
     */
    public function archive(DateTimeInterface $cutoff, int $batchSize = 1000, bool $dryRun = false): array
    {
        $cutoffAt = CarbonImmutable::instance($cutoff);

        $result = [
            'dry_run' => $dryRun,
            'cutoff' => $cutoffAt->toDateTimeString(),
            'batch_size' => $batchSize,
            'candidates' => [
                'ventes' => $this->venteCandidateCount($cutoffAt),
                'commandes' => $this->commandeCandidateCount($cutoffAt),
            ],
            'archived' => [
                'ventes' => 0,
                'vente_details' => 0,
                'commandes' => 0,
                'commande_details' => 0,
                'paiements' => 0,
            ],
            'deleted' => [
                'ventes' => 0,
                'vente_details' => 0,
                'commandes' => 0,
                'commande_details' => 0,
                'paiements' => 0,
            ],
            'batches' => [
                'ventes' => 0,
                'commandes' => 0,
            ],
        ];

        if ($dryRun) {
            return $result;
        }

        $this->archiveVentes($cutoffAt, $batchSize, $result);
        $this->archiveCommandes($cutoffAt, $batchSize, $result);

        return $result;
    }

    /**
     * @param array<string, int|string|bool|array<string, int>> $result
     */
    private function archiveVentes(CarbonImmutable $cutoffAt, int $batchSize, array &$result): void
    {
        while (true) {
            $ids = DB::table('ventes')
                ->where('created_at', '<', $cutoffAt)
                ->whereIn('status', self::VENTE_CLOSED)
                ->orderBy('id')
                ->limit($batchSize)
                ->pluck('id')
                ->map(static fn ($id) => (int) $id)
                ->all();

            if ($ids === []) {
                break;
            }

            $batchResult = DB::transaction(function () use ($ids): array {
                $source = [
                    'ventes' => (int) DB::table('ventes')->whereIn('id', $ids)->count(),
                    'vente_details' => (int) DB::table('vente_details')->whereIn('vente_id', $ids)->count(),
                    'paiements' => (int) DB::table('paiements')->whereIn('vente_id', $ids)->count(),
                ];

                $columnsVentes = implode(', ', self::VENTE_COLUMNS);
                $columnsVenteDetails = implode(', ', self::VENTE_DETAIL_COLUMNS);
                $columnsPaiements = implode(', ', self::PAIEMENT_COLUMNS);
                $idList = implode(',', $ids);

                $archived = [
                    'ventes' => DB::affectingStatement("INSERT INTO archive.ventes ({$columnsVentes}) SELECT {$columnsVentes} FROM ventes WHERE id IN ({$idList}) ON CONFLICT (id) DO NOTHING"),
                    'vente_details' => DB::affectingStatement("INSERT INTO archive.vente_details ({$columnsVenteDetails}) SELECT {$columnsVenteDetails} FROM vente_details WHERE vente_id IN ({$idList}) ON CONFLICT (id) DO NOTHING"),
                    'paiements' => DB::affectingStatement("INSERT INTO archive.paiements ({$columnsPaiements}) SELECT {$columnsPaiements} FROM paiements WHERE vente_id IN ({$idList}) ON CONFLICT (id) DO NOTHING"),
                ];

                $archiveCounts = [
                    'ventes' => (int) DB::table('archive.ventes')->whereIn('id', $ids)->count(),
                    'vente_details' => (int) DB::table('archive.vente_details')->whereIn('vente_id', $ids)->count(),
                    'paiements' => (int) DB::table('archive.paiements')->whereIn('vente_id', $ids)->count(),
                ];

                foreach ($source as $key => $value) {
                    if ($archiveCounts[$key] < $value) {
                        throw new RuntimeException("Archive verification failed for {$key} batch.");
                    }
                }

                $deleted = [
                    'paiements' => DB::table('paiements')->whereIn('vente_id', $ids)->delete(),
                    'vente_details' => DB::table('vente_details')->whereIn('vente_id', $ids)->delete(),
                    'ventes' => DB::table('ventes')->whereIn('id', $ids)->delete(),
                ];

                if ((int) $deleted['paiements'] !== $source['paiements']
                    || (int) $deleted['vente_details'] !== $source['vente_details']
                    || (int) $deleted['ventes'] !== $source['ventes']) {
                    throw new RuntimeException('Delete verification failed for ventes batch.');
                }

                return [
                    'archived' => $archived,
                    'deleted' => $deleted,
                ];
            });

            $result['archived']['ventes'] += (int) $batchResult['archived']['ventes'];
            $result['archived']['vente_details'] += (int) $batchResult['archived']['vente_details'];
            $result['archived']['paiements'] += (int) $batchResult['archived']['paiements'];
            $result['deleted']['ventes'] += (int) $batchResult['deleted']['ventes'];
            $result['deleted']['vente_details'] += (int) $batchResult['deleted']['vente_details'];
            $result['deleted']['paiements'] += (int) $batchResult['deleted']['paiements'];
            $result['batches']['ventes']++;
        }
    }

    /**
     * @param array<string, int|string|bool|array<string, int>> $result
     */
    private function archiveCommandes(CarbonImmutable $cutoffAt, int $batchSize, array &$result): void
    {
        while (true) {
            $ids = DB::table('commandes')
                ->where('created_at', '<', $cutoffAt)
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->where('type', 'kitchen')
                          ->whereIn('status', self::KITCHEN_COMMANDE_CLOSED);
                    })->orWhere(function ($q) {
                        $q->where('type', 'supplier')
                          ->whereIn('status', self::SUPPLIER_COMMANDE_CLOSED);
                    });
                })
                ->orderBy('id')
                ->limit($batchSize)
                ->pluck('id')
                ->map(static fn ($id) => (int) $id)
                ->all();

            if ($ids === []) {
                break;
            }

            $batchResult = DB::transaction(function () use ($ids): array {
                $source = [
                    'commandes' => (int) DB::table('commandes')->whereIn('id', $ids)->count(),
                    'commande_details' => (int) DB::table('commande_details')->whereIn('commande_id', $ids)->count(),
                    'paiements' => (int) DB::table('paiements')->whereIn('commande_id', $ids)->count(),
                ];

                $columnsCommandes = implode(', ', self::COMMANDE_COLUMNS);
                $columnsCommandeDetails = implode(', ', self::COMMANDE_DETAIL_COLUMNS);
                $columnsPaiements = implode(', ', self::PAIEMENT_COLUMNS);
                $idList = implode(',', $ids);

                $archived = [
                    'commandes' => DB::affectingStatement("INSERT INTO archive.commandes ({$columnsCommandes}) SELECT {$columnsCommandes} FROM commandes WHERE id IN ({$idList}) ON CONFLICT (id) DO NOTHING"),
                    'commande_details' => DB::affectingStatement("INSERT INTO archive.commande_details ({$columnsCommandeDetails}) SELECT {$columnsCommandeDetails} FROM commande_details WHERE commande_id IN ({$idList}) ON CONFLICT (id) DO NOTHING"),
                    'paiements' => DB::affectingStatement("INSERT INTO archive.paiements ({$columnsPaiements}) SELECT {$columnsPaiements} FROM paiements WHERE commande_id IN ({$idList}) ON CONFLICT (id) DO NOTHING"),
                ];

                $archiveCounts = [
                    'commandes' => (int) DB::table('archive.commandes')->whereIn('id', $ids)->count(),
                    'commande_details' => (int) DB::table('archive.commande_details')->whereIn('commande_id', $ids)->count(),
                    'paiements' => (int) DB::table('archive.paiements')->whereIn('commande_id', $ids)->count(),
                ];

                foreach ($source as $key => $value) {
                    if ($archiveCounts[$key] < $value) {
                        throw new RuntimeException("Archive verification failed for {$key} batch.");
                    }
                }

                $deleted = [
                    'paiements' => DB::table('paiements')->whereIn('commande_id', $ids)->delete(),
                    'commande_details' => DB::table('commande_details')->whereIn('commande_id', $ids)->delete(),
                    'commandes' => DB::table('commandes')->whereIn('id', $ids)->delete(),
                ];

                if ((int) $deleted['paiements'] !== $source['paiements']
                    || (int) $deleted['commande_details'] !== $source['commande_details']
                    || (int) $deleted['commandes'] !== $source['commandes']) {
                    throw new RuntimeException('Delete verification failed for commandes batch.');
                }

                return [
                    'archived' => $archived,
                    'deleted' => $deleted,
                ];
            });

            $result['archived']['commandes'] += (int) $batchResult['archived']['commandes'];
            $result['archived']['commande_details'] += (int) $batchResult['archived']['commande_details'];
            $result['archived']['paiements'] += (int) $batchResult['archived']['paiements'];
            $result['deleted']['commandes'] += (int) $batchResult['deleted']['commandes'];
            $result['deleted']['commande_details'] += (int) $batchResult['deleted']['commande_details'];
            $result['deleted']['paiements'] += (int) $batchResult['deleted']['paiements'];
            $result['batches']['commandes']++;
        }
    }

    private function venteCandidateCount(CarbonImmutable $cutoffAt): int
    {
        return (int) DB::table('ventes')
            ->where('created_at', '<', $cutoffAt)
            ->whereIn('status', self::VENTE_CLOSED)
            ->count();
    }

    private function commandeCandidateCount(CarbonImmutable $cutoffAt): int
    {
        return (int) DB::table('commandes')
            ->where('created_at', '<', $cutoffAt)
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('type', 'kitchen')
                      ->whereIn('status', self::KITCHEN_COMMANDE_CLOSED);
                })->orWhere(function ($q) {
                    $q->where('type', 'supplier')
                      ->whereIn('status', self::SUPPLIER_COMMANDE_CLOSED);
                });
            })
            ->count();
    }
}

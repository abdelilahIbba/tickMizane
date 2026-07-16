<?php

namespace App\Services\Archiving;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MonthlyHistoriqueArchiver
{
    /**
     * @var string[]
     */
    private const COLUMNS = [
        'id',
        'user_id',
        'role',
        'action',
        'table_name',
        'record_id',
        'description',
        'created_at',
        'updated_at',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'device_type',
    ];

    /**
     * Archive historique rows older than cutoff and remove them from hot table.
     *
     * @return array<string, int|string|bool>
     */
    public function archive(DateTimeInterface $cutoff, int $batchSize = 2000, bool $dryRun = false): array
    {
        $cutoffAt = CarbonImmutable::instance($cutoff);
        $candidates = (int) DB::table('historiques')->where('created_at', '<', $cutoffAt)->count();

        $result = [
            'dry_run' => $dryRun,
            'cutoff' => $cutoffAt->toDateTimeString(),
            'batch_size' => $batchSize,
            'candidates' => $candidates,
            'archived' => 0,
            'deleted' => 0,
            'batches' => 0,
        ];

        if ($dryRun || $candidates === 0) {
            return $result;
        }

        while (true) {
            $ids = DB::table('historiques')
                ->where('created_at', '<', $cutoffAt)
                ->orderBy('id')
                ->limit($batchSize)
                ->pluck('id')
                ->map(static fn ($id) => (int) $id)
                ->all();

            if ($ids === []) {
                break;
            }

            [$inserted, $deleted] = DB::transaction(function () use ($ids): array {
                $idList = implode(',', $ids);
                $columns = implode(', ', self::COLUMNS);

                $inserted = DB::affectingStatement(
                    "INSERT INTO archive.historiques ({$columns}) SELECT {$columns} FROM historiques WHERE id IN ({$idList}) ON CONFLICT (id) DO NOTHING"
                );

                $sourceCount = (int) DB::table('historiques')->whereIn('id', $ids)->count();
                $archiveCount = (int) DB::table('archive.historiques')->whereIn('id', $ids)->count();

                if ($archiveCount < $sourceCount) {
                    throw new RuntimeException('Archive verification failed: not all rows were copied to archive.historiques.');
                }

                $deleted = DB::table('historiques')->whereIn('id', $ids)->delete();

                if ($deleted !== $sourceCount) {
                    throw new RuntimeException('Archive verification failed: delete count does not match source count.');
                }

                return [$inserted, $deleted];
            });

            $result['archived'] += $inserted;
            $result['deleted'] += $deleted;
            $result['batches']++;
        }

        return $result;
    }
}

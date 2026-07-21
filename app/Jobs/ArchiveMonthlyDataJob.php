<?php

namespace App\Jobs;

use App\Services\Archiving\MonthlyHistoriqueArchiver;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * ArchiveMonthlyDataJob - مهمة أرشفة سجلات الأحداث الشهرية
 *
 * مهمة مجدولة تُشغَّل شهرياً لأرشفة جدول historiques
 * القديمة إلى جدول archive_historiques.
 *
 * الإعدادات:
 * - tries   = 3   : إعادة المحاولة ثلاث مرات عند الفشل
 * - timeout = 1200: المهلة 20 دقيقة
 * - backoff  = 60, 300, 900 ثانية بين المحاولات
 *
 * @see MonthlyHistoriqueArchiver
 */
class ArchiveMonthlyDataJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 1200;

    /**
     * @var int[]
     */
    public array $backoff = [60, 300, 900];

    public function __construct(
        public string $cutoffIso,
        public int $batchSize = 2000,
        public bool $dryRun = false,
    ) {
    }

    public function handle(MonthlyHistoriqueArchiver $archiver): void
    {
        $result = $archiver->archive(
            cutoff: CarbonImmutable::parse($this->cutoffIso),
            batchSize: $this->batchSize,
            dryRun: $this->dryRun,
        );

        Log::info('Monthly archive job completed', $result);
    }
}

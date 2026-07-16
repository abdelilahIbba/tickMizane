<?php

namespace App\Jobs;

use App\Services\Archiving\MonthlyFinancialArchiver;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ArchiveMonthlyFinancialDataJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 1800;

    /**
     * @var int[]
     */
    public array $backoff = [60, 300, 900];

    public function __construct(
        public string $cutoffIso,
        public int $batchSize = 1000,
        public bool $dryRun = false,
    ) {
    }

    public function handle(MonthlyFinancialArchiver $archiver): void
    {
        $result = $archiver->archive(
            cutoff: CarbonImmutable::parse($this->cutoffIso),
            batchSize: $this->batchSize,
            dryRun: $this->dryRun,
        );

        Log::info('Monthly financial archive job completed', $result);
    }
}

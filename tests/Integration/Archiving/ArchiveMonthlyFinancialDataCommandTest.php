<?php

namespace Tests\Integration\Archiving;

use App\Jobs\ArchiveMonthlyFinancialDataJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ArchiveMonthlyFinancialDataCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function dry_run_command_executes_successfully(): void
    {
        $exitCode = Artisan::call('archive:financial-monthly', [
            '--cutoff-months' => 18,
            '--batch' => 500,
            '--dry-run' => true,
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('dry_run', $output);
        $this->assertStringContainsString('candidates.ventes', $output);
        $this->assertStringContainsString('candidates.commandes', $output);
    }

    #[Test]
    public function queue_option_dispatches_financial_archive_job(): void
    {
        Queue::fake();

        $exitCode = Artisan::call('archive:financial-monthly', [
            '--queue' => true,
            '--cutoff-months' => 18,
            '--batch' => 500,
            '--dry-run' => true,
        ]);

        $this->assertSame(0, $exitCode);

        Queue::assertPushed(ArchiveMonthlyFinancialDataJob::class, function (ArchiveMonthlyFinancialDataJob $job): bool {
            return $job->batchSize === 500 && $job->dryRun === true;
        });
    }
}

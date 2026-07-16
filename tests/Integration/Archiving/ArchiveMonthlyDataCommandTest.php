<?php

namespace Tests\Integration\Archiving;

use App\Jobs\ArchiveMonthlyDataJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ArchiveMonthlyDataCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function dry_run_command_executes_successfully(): void
    {
        $exitCode = Artisan::call('archive:monthly', [
            '--cutoff-months' => 12,
            '--batch' => 500,
            '--dry-run' => true,
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('dry_run', $output);
        $this->assertStringContainsString('candidates', $output);
        $this->assertStringContainsString('archived', $output);
    }

    #[Test]
    public function queue_option_dispatches_historiques_archive_job(): void
    {
        Queue::fake();

        $exitCode = Artisan::call('archive:monthly', [
            '--queue' => true,
            '--cutoff-months' => 12,
            '--batch' => 500,
            '--dry-run' => true,
        ]);

        $this->assertSame(0, $exitCode);

        Queue::assertPushed(ArchiveMonthlyDataJob::class, function (ArchiveMonthlyDataJob $job): bool {
            return $job->batchSize === 500 && $job->dryRun === true;
        });
    }
}

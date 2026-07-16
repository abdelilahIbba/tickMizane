<?php

namespace Tests\Integration\Archiving;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VerifyFinancialArchiveCommandTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function verify_command_succeeds_when_archive_state_is_clean(): void
    {
        $exitCode = Artisan::call('archive:verify-financial', [
            '--cutoff-months' => 18,
        ]);

        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Financial archive verification report', $output);
        $this->assertStringContainsString('orphan_archive_rows.paiements_without_parent', $output);
    }

    #[Test]
    public function verify_command_fails_with_clear_message_if_archive_tables_are_missing(): void
    {
        DB::statement('DROP TABLE IF EXISTS archive.ventes');

        $exitCode = Artisan::call('archive:verify-financial', [
            '--cutoff-months' => 18,
        ]);

        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Archive financial tables are missing', $output);
        $this->assertStringContainsString('Run migrations then retry', $output);
    }
}

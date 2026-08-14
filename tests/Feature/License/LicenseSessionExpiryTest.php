<?php

namespace Tests\Feature\License;

use App\Models\License;
use App\Models\User;
use App\Services\LicenseService;
use App\Support\SuperAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LicenseSessionExpiryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    #[Test]
    public function active_session_is_blocked_after_license_expires(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'force_password_reset' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk();

        $this->clearLicensesForTests();
        License::factory()->expired()->create(['client_name' => 'Expired Client']);
        app(LicenseService::class)->forgetCache();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertRedirect(route('license.blocked'));

        $this->actingAs($admin)
            ->get(route('license.blocked'))
            ->assertOk()
            ->assertSee('DevNApp', false)
            ->assertSee('contactez', false);
    }

    #[Test]
    public function reactivating_license_restores_access_without_relogin(): void
    {
        $this->clearLicensesForTests();

        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'force_password_reset' => false,
        ]);

        $license = License::factory()->create([
            'client_name' => 'Renew Client',
            'period' => License::PERIOD_1_WEEK,
            'status' => License::STATUS_CREATED,
            'is_activated' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertRedirect(route('license.blocked'));

        $this->actingAs(SuperAdmin::make())
            ->post(route('settings.licenses.activate', $license))
            ->assertRedirect(route('settings.licenses.index'));

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk();
    }

    #[Test]
    public function super_admin_remains_unrestricted_when_license_is_expired(): void
    {
        $this->clearLicensesForTests();
        License::factory()->expired()->create();

        $this->actingAs(SuperAdmin::make())
            ->get(route('settings.licenses.index'))
            ->assertOk();

        $this->actingAs(SuperAdmin::make())
            ->get(route('dashboard'))
            ->assertOk();
    }
}

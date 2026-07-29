<?php

namespace Tests\Feature\License;

use App\Models\License;
use App\Models\User;
use App\Support\SuperAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LicenseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    #[Test]
    public function super_admin_can_login_with_fixed_pin_without_users_row(): void
    {
        $this->clearLicensesForTests();

        $response = $this->post(route('login.submit'), [
            'password' => SuperAdmin::pin(),
        ]);

        $response->assertRedirect(route('settings.licenses.index'));
        $this->assertAuthenticated();
        $this->assertTrue(SuperAdmin::is(auth()->user()));
        $this->assertDatabaseMissing('users', ['username' => SuperAdmin::username()]);
    }

    #[Test]
    public function regular_users_cannot_login_without_active_license(): void
    {
        $this->clearLicensesForTests();

        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'password' => bcrypt('482917'),
        ]);

        $response = $this->post(route('login.submit'), [
            'password' => '482917',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    #[Test]
    public function regular_users_can_login_when_license_is_active(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'password' => bcrypt('482917'),
            'force_password_reset' => false,
        ]);

        $response = $this->post(route('login.submit'), [
            'password' => '482917',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    #[Test]
    public function only_super_admin_can_manage_licenses(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'force_password_reset' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('settings.licenses.index'))
            ->assertForbidden();

        $this->actingAs(SuperAdmin::make())
            ->get(route('settings.licenses.index'))
            ->assertOk();
    }

    #[Test]
    public function created_license_is_inactive_until_explicit_activation(): void
    {
        $this->clearLicensesForTests();

        $this->actingAs(SuperAdmin::make())
            ->post(route('settings.licenses.store'), [
                'client_name' => 'Hotel Atlas',
                'period' => License::PERIOD_2_WEEKS,
                'notes' => 'Trial',
            ])
            ->assertRedirect(route('settings.licenses.index'));

        $license = License::query()->where('client_name', 'Hotel Atlas')->firstOrFail();

        $this->assertFalse($license->is_activated);
        $this->assertSame(License::STATUS_CREATED, $license->status);
        $this->assertNull($license->expires_at);
        $this->assertFalse($license->isCurrentlyValid());
    }

    #[Test]
    public function activating_license_assigns_period_and_unlocks_system(): void
    {
        $this->clearLicensesForTests();

        $license = License::factory()->create([
            'client_name' => 'Riad Fes',
            'period' => License::PERIOD_1_WEEK,
        ]);

        $this->actingAs(SuperAdmin::make())
            ->post(route('settings.licenses.activate', $license))
            ->assertRedirect(route('settings.licenses.index'));

        $license->refresh();

        $this->assertTrue($license->is_activated);
        $this->assertSame(License::STATUS_ACTIVE, $license->status);
        $this->assertNotNull($license->expires_at);
        $this->assertTrue($license->expires_at->greaterThan(now()->addDays(6)));
    }

    #[Test]
    public function expired_license_blocks_authenticated_non_super_admin_access(): void
    {
        $this->clearLicensesForTests();
        License::factory()->expired()->create();

        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'force_password_reset' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertRedirect(route('license.blocked'));

        $this->actingAs($admin)
            ->get(route('license.blocked'))
            ->assertOk()
            ->assertSee('Système bloqué', false)
            ->assertSee('Contactez DevNApp', false)
            ->assertSee('régler', false);
    }
}

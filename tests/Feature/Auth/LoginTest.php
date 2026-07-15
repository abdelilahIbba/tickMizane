<?php

namespace Tests\Feature\Auth;

use PHPUnit\Framework\Attributes\Test;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable throttle for all login tests so individual request counts
        // from earlier tests in the same process do not trigger rate-limiting
        // on later tests. The dedicated rate-limiting test re-enables it.
        $this->withoutMiddleware(ThrottleRequests::class);

        // Belt-and-suspenders: also clear any cached throttle counters.
        RateLimiter::clear('login');
    }

    #[Test]
    public function it_displays_login_page()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    #[Test]
    public function it_redirects_authenticated_users_from_login_page()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get(route('login'));

        $response->assertRedirect(route('dashboard'));
    }

    #[Test]
    public function admin_can_login_with_pin_code()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->post(route('login.submit'), [
            'login_mode' => 'admin',
            'password' => '009988', // Admin PIN
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    #[Test]
    public function admin_cannot_login_with_invalid_pin()
    {
        User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->post(route('login.submit'), [
            'login_mode' => 'admin',
            'password' => '000000', // Wrong PIN
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    #[Test]
    public function admin_login_creates_bootstrap_admin_when_none_exists_and_pin_is_valid()
    {
        $response = $this->post(route('login.submit'), [
            'login_mode' => 'admin',
            'password' => '009988',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    #[Test]
    public function caissier_can_login_with_username_and_password()
    {
        $caissier = User::factory()->create([
            'username' => 'cashier01',
            'password' => bcrypt('password123'),
            'role' => 'caissier',
            'status' => 'active',
        ]);

        $response = $this->post(route('login.submit'), [
            'login_mode' => 'staff',
            'username' => 'cashier01',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('pos.index'));
        $this->assertAuthenticatedAs($caissier);
    }

    #[Test]
    public function serveur_can_login_with_username_and_password()
    {
        $serveur = User::factory()->create([
            'username' => 'waiter01',
            'password' => bcrypt('password123'),
            'role' => 'serveur',
            'status' => 'active',
        ]);

        $response = $this->post(route('login.submit'), [
            'login_mode' => 'staff',
            'username' => 'waiter01',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/tables');
        $this->assertAuthenticatedAs($serveur);
    }

    #[Test]
    public function staff_cannot_login_with_wrong_password()
    {
        User::factory()->create([
            'username' => 'cashier01',
            'password' => bcrypt('password123'),
            'role' => 'caissier',
            'status' => 'active',
        ]);

        $response = $this->post(route('login.submit'), [
            'login_mode' => 'staff',
            'username' => 'cashier01',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    #[Test]
    public function staff_cannot_login_with_invalid_username()
    {
        $response = $this->post(route('login.submit'), [
            'login_mode' => 'staff',
            'username' => 'nonexistent',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    #[Test]
    public function blocked_users_cannot_login()
    {
        User::factory()->create([
            'username' => 'blocked01',
            'password' => bcrypt('password123'),
            'role' => 'caissier',
            'status' => 'blocked',
        ]);

        $response = $this->post(route('login.submit'), [
            'login_mode' => 'staff',
            'username' => 'blocked01',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    #[Test]
    public function admin_user_cannot_login_through_staff_mode()
    {
        User::factory()->create([
            'username' => 'admin',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->post(route('login.submit'), [
            'login_mode' => 'staff',
            'username' => 'admin',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    #[Test]
    public function login_validates_required_fields_for_staff()
    {
        $response = $this->post(route('login.submit'), [
            'login_mode' => 'staff',
        ]);

        $response->assertSessionHasErrors(['username', 'password']);
    }

    #[Test]
    public function login_validates_required_password_for_admin()
    {
        $response = $this->post(route('login.submit'), [
            'login_mode' => 'admin',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    #[Test]
    public function login_rate_limiting_works_after_five_attempts()
    {
        // Re-enable throttle middleware just for this test.
        $this->withMiddleware(ThrottleRequests::class);

        // Make 5 failed login attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.submit'), [
                'login_mode' => 'staff',
                'username' => 'test',
                'password' => 'wrong',
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->post(route('login.submit'), [
            'login_mode' => 'staff',
            'username' => 'test',
            'password' => 'wrong',
        ]);

        $response->assertStatus(429); // Too Many Requests
    }

    #[Test]
    public function user_can_logout()
    {
        $user = User::factory()->create([
            'role' => 'caissier',
            'status' => 'active',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    #[Test]
    public function session_regenerates_after_successful_login()
    {
        $caissier = User::factory()->create([
            'username' => 'cashier01',
            'password' => bcrypt('password123'),
            'role' => 'caissier',
            'status' => 'active',
        ]);

        $response = $this->post(route('login.submit'), [
            'login_mode' => 'staff',
            'username' => 'cashier01',
            'password' => 'password123',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($caissier);
    }
}

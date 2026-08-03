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
        /** @var User $admin */
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
            'password' => bcrypt('483921'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->post(route('login.submit'), [
            'password' => '483921',
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
            'password' => '000000', // Wrong PIN
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    #[Test]
    public function login_cannot_bootstrap_an_admin_with_a_shared_pin()
    {
        $response = $this->post(route('login.submit'), [
            'password' => '483921',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['role' => 'admin']);
    }

    #[Test]
    public function caissier_can_login_with_pin_and_reach_kitchen()
    {
        $caissier = User::factory()->create([
            'username' => 'cashier01',
            'password' => bcrypt('123456'),
            'role' => 'caissier',
            'status' => 'active',
        ]);

        $response = $this->post(route('login.submit'), [
            'password' => '123456',
        ]);

        $response->assertRedirect(route('kitchen.index'));
        $this->assertAuthenticatedAs($caissier);
    }

    #[Test]
    public function serveur_can_login_with_pin_and_reach_waiter()
    {
        $serveur = User::factory()->create([
            'username' => 'waiter01',
            'password' => bcrypt('112233'),
            'role' => 'serveur',
            'status' => 'active',
        ]);

        $response = $this->post(route('login.submit'), [
            'password' => '112233',
        ]);

        $response->assertRedirect(route('waiter.index'));
        $this->assertAuthenticatedAs($serveur);
    }

    #[Test]
    public function user_cannot_login_with_wrong_pin()
    {
        User::factory()->create([
            'username' => 'cashier01',
            'password' => bcrypt('123456'),
            'role' => 'caissier',
            'status' => 'active',
        ]);

        $response = $this->post(route('login.submit'), [
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    #[Test]
    public function unknown_pin_cannot_login()
    {
        $response = $this->post(route('login.submit'), [
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    #[Test]
    public function blocked_users_cannot_login()
    {
        User::factory()->create([
            'username' => 'blocked01',
            'password' => bcrypt('556677'),
            'role' => 'caissier',
            'status' => 'blocked',
        ]);

        $response = $this->post(route('login.submit'), [
            'password' => '556677',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    #[Test]
    public function admin_user_can_login_with_hashed_pin()
    {
        $admin = User::factory()->create([
            'username' => 'admin',
            'password' => bcrypt('445566'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->post(route('login.submit'), [
            'password' => '445566',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    #[Test]
    public function login_validates_required_password_for_pin_login()
    {
        $response = $this->post(route('login.submit'), []);

        $response->assertSessionHasErrors('password');
    }

    #[Test]
    public function login_rate_limiting_works_after_five_attempts()
    {
        // Re-enable throttle middleware just for this test.
        $this->withMiddleware(ThrottleRequests::class);

        // Make 5 failed login attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.submit'), [
                'password' => 'wrong',
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->post(route('login.submit'), [
            'password' => 'wrong',
        ]);

        $response->assertStatus(429); // Too Many Requests
    }

    #[Test]
    public function api_login_is_rate_limited_after_five_attempts()
    {
        $this->withMiddleware(ThrottleRequests::class);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'username' => 'unknown',
                'password' => 'wrong',
            ]);
        }

        $this->postJson('/api/v1/auth/login', [
            'username' => 'unknown',
            'password' => 'wrong',
        ])->assertStatus(429);
    }

    #[Test]
    public function user_can_logout()
    {
        /** @var User $user */
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
            'password' => bcrypt('778899'),
            'role' => 'caissier',
            'status' => 'active',
        ]);

        $response = $this->post(route('login.submit'), [
            'password' => '778899',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($caissier);
    }
}

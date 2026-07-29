<?php

namespace Tests\Feature\Settings;

use PHPUnit\Framework\Attributes\Test;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
    }

    #[Test]
    public function admin_can_view_users_list(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('settings.users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('settings.users.index');
    }

    #[Test]
    public function admin_can_create_user(): void
    {
        
        $response = $this->actingAs($this->admin)
            ->post(route('settings.users.store'), [
                'name' => 'Test User',
                'username' => 'testuser',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'serveur',
                'status' => 'active',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'username' => 'testuser',
            'role' => 'serveur',
        ]);

        $created = User::where('username', 'testuser')->first();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('password123', $created->password));
    }

    #[Test]
    public function admin_can_delete_user(): void
    {
        $user = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('settings.users.destroy', $user));

        $response->assertRedirect(route('settings.users.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    #[Test]
    public function admin_can_update_user(): void
    {
        
        $user = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('settings.users.update', $user), [
                'name' => 'Updated Name',
                'username' => $user->username,
                'role' => 'caissier',
                'status' => 'active',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'role' => 'caissier',
        ]);
    }

    #[Test]
    public function admin_can_deactivate_user(): void
    {
        
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('settings.users.deactivate', $user));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'blocked',
        ]);
    }

    #[Test]
    public function admin_can_activate_user(): void
    {
        
        $user = User::factory()->create([
            'status' => 'blocked',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('settings.users.activate', $user));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'active',
        ]);
    }

    #[Test]
    public function admin_cannot_deactivate_themselves(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('settings.users.deactivate', $this->admin));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'status' => 'active',
        ]);
    }

    #[Test]
    public function admin_cannot_delete_themselves(): void
    {
        $response = $this->actingAs($this->admin)
            ->delete(route('settings.users.destroy', $this->admin));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
        ]);
    }

    #[Test]
    public function non_admin_cannot_access_user_management(): void
    {
        $serveur = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
        ]);

        $response = $this->actingAs($serveur)
            ->get(route('settings.users.index'));

        $response->assertStatus(302); // Redirected
    }

    #[Test]
    public function password_reset_sets_force_flag(): void
    {
        
        $user = User::factory()->create([
            'force_password_reset' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('settings.users.reset-password.submit', $user), [
                'force_reset' => true,
            ]);

        $response->assertRedirect();
        $user->refresh();
        $this->assertTrue($user->force_password_reset);
    }
}

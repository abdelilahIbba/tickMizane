<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForcePasswordResetTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_with_force_reset_is_redirected_to_password_change(): void
    {
        $user = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
            'force_password_reset' => true,
        ]);

        $response = $this->actingAs($user)
            ->get(route('waiter.index'));

        $response->assertRedirect(route('password.change'));
    }

    #[Test]
    public function user_without_force_reset_can_access_pages(): void
    {
        $user = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
            'force_password_reset' => false,
        ]);

        $response = $this->actingAs($user)
            ->get(route('waiter.index'));

        $response->assertStatus(200);
    }

    #[Test]
    public function user_can_change_password(): void
    {
        $user = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
            'password' => Hash::make('oldpassword'),
            'force_password_reset' => false,
        ]);

        $response = $this->actingAs($user)
            ->post(route('password.change.submit'), [
                'current_password' => 'oldpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect();
        
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
        $this->assertFalse($user->force_password_reset);
    }

    #[Test]
    public function force_reset_user_can_change_without_current_password(): void
    {
        $user = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
            'force_password_reset' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('password.change.submit'), [
                'current_password' => 'skip', // Special value for forced resets
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect();
        
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
        $this->assertFalse($user->force_password_reset);
    }

    #[Test]
    public function wrong_current_password_fails(): void
    {
        $user = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
            'password' => Hash::make('correctpassword'),
            'force_password_reset' => false,
        ]);

        $response = $this->actingAs($user)
            ->post(route('password.change.submit'), [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertSessionHasErrors('current_password');
    }

    #[Test]
    public function password_confirmation_must_match(): void
    {
        $user = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
            'password' => Hash::make('oldpassword'),
            'force_password_reset' => false,
        ]);

        $response = $this->actingAs($user)
            ->post(route('password.change.submit'), [
                'current_password' => 'oldpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'differentpassword',
            ]);

        $response->assertSessionHasErrors('password');
    }

    #[Test]
    public function password_change_is_logged(): void
    {
        $user = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
            'password' => Hash::make('oldpassword'),
            'force_password_reset' => false,
        ]);

        $this->actingAs($user)
            ->post(route('password.change.submit'), [
                'current_password' => 'oldpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $this->assertDatabaseHas('historiques', [
            'action' => 'password_changed',
            'table_name' => 'users',
            'record_id' => $user->id,
        ]);
    }
}

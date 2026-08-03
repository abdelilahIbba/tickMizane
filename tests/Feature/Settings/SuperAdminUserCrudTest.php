<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use App\Support\SuperAdmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SuperAdminUserCrudTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function super_admin_can_create_and_hard_delete_user(): void
    {
        $response = $this->actingAs(SuperAdmin::make())
            ->from(route('settings.users.create'))
            ->post(route('settings.users.store'), [
                'name' => 'Client Staff',
                'username' => 'clientstaff',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'serveur',
                'status' => 'active',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionMissing('error');
        $response->assertRedirect(route('settings.users.index'));

        $user = User::where('username', 'clientstaff')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('password123', $user->password));

        $delete = $this->actingAs(SuperAdmin::make())
            ->from(route('settings.users.index'))
            ->delete(route('settings.users.destroy', $user));

        $delete->assertSessionHasNoErrors();
        $delete->assertSessionMissing('error');
        $delete->assertRedirect(route('settings.users.index'));
        $this->assertDatabaseMissing('users', ['username' => 'clientstaff']);
    }

    #[Test]
    public function delete_is_blocked_when_user_has_related_orders(): void
    {
        $staff = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
        ]);

        \App\Models\Commande::factory()->create([
            'user_id' => $staff->id,
            'type' => 'kitchen',
        ]);

        $response = $this->actingAs(SuperAdmin::make())
            ->from(route('settings.users.index'))
            ->delete(route('settings.users.destroy', $staff));

        $response->assertRedirect(route('settings.users.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $staff->id]);
    }
}

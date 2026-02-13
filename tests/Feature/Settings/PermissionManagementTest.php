<?php

namespace Tests\Feature\Settings;

use PHPUnit\Framework\Attributes\Test;

use App\Models\User;
use App\Models\UserPermission;
use App\Services\PermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected PermissionService $permissionService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        
        $this->permissionService = app(PermissionService::class);
    }

    #[Test]
    public function admin_can_view_permissions_list(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('settings.permissions.index'));

        $response->assertStatus(200);
        $response->assertViewIs('settings.permissions.index');
    }

    #[Test]
    public function admin_can_view_user_permissions(): void
    {
        $user = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('settings.permissions.show', $user));

        $response->assertStatus(200);
        $response->assertViewIs('settings.permissions.show');
    }

    #[Test]
    public function admin_can_update_user_permissions(): void
    {
        
        $user = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('settings.permissions.update', $user), [
                'permissions' => [
                    'pos' => ['view' => true, 'create' => true],
                    'kitchen' => ['view' => true],
                ],
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify permissions were saved
        $this->assertTrue($this->permissionService->hasPermission($user, 'pos', 'view'));
        $this->assertTrue($this->permissionService->hasPermission($user, 'pos', 'create'));
        $this->assertTrue($this->permissionService->hasPermission($user, 'kitchen', 'view'));
    }

    #[Test]
    public function admin_can_grant_all_permissions(): void
    {
        
        $user = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('settings.permissions.grant-all', $user));

        $response->assertRedirect();

        // Verify all permissions granted
        $this->assertTrue($this->permissionService->hasPermission($user, 'pos', 'view'));
        $this->assertTrue($this->permissionService->hasPermission($user, 'kitchen', 'create'));
        $this->assertTrue($this->permissionService->hasPermission($user, 'settings', 'delete'));
    }

    #[Test]
    public function admin_can_revoke_all_permissions(): void
    {
        
        $user = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
        ]);

        // First grant some permissions
        $this->permissionService->setPermission($user, 'pos', 'view', true);
        $this->permissionService->setPermission($user, 'kitchen', 'view', true);

        $response = $this->actingAs($this->admin)
            ->post(route('settings.permissions.revoke-all', $user));

        $response->assertRedirect();

        // Verify all permissions revoked
        $this->assertFalse($this->permissionService->hasPermission($user, 'pos', 'view'));
        $this->assertFalse($this->permissionService->hasPermission($user, 'kitchen', 'view'));
    }

    #[Test]
    public function admin_always_has_all_permissions(): void
    {
        // Admin should have all permissions regardless of database
        $this->assertTrue($this->permissionService->hasPermission($this->admin, 'pos', 'view'));
        $this->assertTrue($this->permissionService->hasPermission($this->admin, 'settings', 'delete'));
        $this->assertTrue($this->permissionService->hasPermission($this->admin, 'any_module', 'any_action'));
    }

    #[Test]
    public function non_admin_cannot_access_permissions(): void
    {
        $serveur = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
        ]);

        $response = $this->actingAs($serveur)
            ->get(route('settings.permissions.index'));

        $response->assertStatus(302); // Redirected
    }

    #[Test]
    public function permission_changes_are_logged(): void
    {
        
        $user = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
        ]);

        // First create a permission
        $this->permissionService->setPermission($user, 'pos', 'view', false);

        // Then update it
        $this->actingAs($this->admin)
            ->postJson(route('settings.permissions.update', $user), [
                'permissions' => [
                    'pos' => ['view' => true],
                ],
            ]);

        // Should have logged the update
        $this->assertDatabaseHas('historiques', [
            'action' => 'updated',
            'table_name' => 'user_permissions',
        ]);
    }
}

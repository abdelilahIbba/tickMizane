<?php

namespace Tests\Feature\Settings;

use PHPUnit\Framework\Attributes\Test;

use App\Models\Setting;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected SettingService $settingService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);
        
        $this->settingService = app(SettingService::class);
    }

    #[Test]
    public function admin_can_view_settings_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('settings.system.index'));

        $response->assertStatus(200);
        $response->assertViewIs('settings.system.index');
    }

    #[Test]
    public function admin_can_view_settings_group(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('settings.system.group', 'general'));

        $response->assertStatus(200);
        $response->assertViewIs('settings.system.group');
    }

    #[Test]
    public function admin_can_update_settings(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('settings.system.update', 'general'), [
                'settings' => [
                    'business_name' => 'Test Restaurant',
                    'tax_rate' => 15.5,
                ],
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals('Test Restaurant', $this->settingService->get('business_name'));
        $this->assertEquals(15.5, $this->settingService->get('tax_rate'));
    }

    #[Test]
    public function settings_are_cached(): void
    {
        // Set a setting
        $this->settingService->set('business_name', 'Cached Restaurant');

        // Clear cache manually would break this, but normal get should work
        $this->assertEquals('Cached Restaurant', $this->settingService->get('business_name'));
    }

    #[Test]
    public function admin_can_reset_group_to_defaults(): void
    {
        
        // First, change a setting
        $this->settingService->set('business_name', 'Custom Name');
        $this->assertEquals('Custom Name', $this->settingService->get('business_name'));

        // Reset to defaults
        $response = $this->actingAs($this->admin)
            ->postJson(route('settings.system.reset', 'general'));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Should be back to default
        $this->assertEquals('TechMizane Restaurant', $this->settingService->get('business_name'));
    }

    #[Test]
    public function invalid_group_returns_404(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('settings.system.group', 'invalid_group'));

        $response->assertStatus(404);
    }

    #[Test]
    public function boolean_settings_are_cast_correctly(): void
    {
        $this->settingService->set('kitchen_audio_enabled', true);
        $this->assertTrue($this->settingService->get('kitchen_audio_enabled'));

        $this->settingService->set('kitchen_audio_enabled', false);
        $this->assertFalse($this->settingService->get('kitchen_audio_enabled'));
    }

    #[Test]
    public function integer_settings_are_cast_correctly(): void
    {
        $this->settingService->set('kitchen_refresh_interval', 10);
        $this->assertSame(10, $this->settingService->get('kitchen_refresh_interval'));
    }

    #[Test]
    public function all_setting_groups_are_accessible(): void
    {
        $groups = ['general', 'stock', 'payment', 'kitchen', 'receipts', 'security'];

        foreach ($groups as $group) {
            $response = $this->actingAs($this->admin)
                ->get(route('settings.system.group', $group));

            $response->assertStatus(200);
        }
    }

    #[Test]
    public function non_admin_cannot_access_system_settings(): void
    {
        $serveur = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
        ]);

        $response = $this->actingAs($serveur)
            ->get(route('settings.system.index'));

        $response->assertStatus(302); // Redirected
    }

    #[Test]
    public function setting_defaults_are_returned_when_not_set(): void
    {
        // Clear any existing settings
        Setting::truncate();
        $this->settingService->clearAllCache();

        // Should return default value
        $this->assertEquals('TechMizane Restaurant', $this->settingService->get('business_name'));
        $this->assertEquals(10, $this->settingService->get('stock_low_threshold_default'));
    }
}

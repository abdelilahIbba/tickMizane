<?php

namespace Tests;

use App\Models\License;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->ensureActiveLicenseForTests();
    }

    /**
     * Keep the majority of feature tests focused on business flows by providing
     * a valid license unless a test explicitly revokes/expires it.
     */
    protected function ensureActiveLicenseForTests(): void
    {
        if (!Schema::hasTable('licenses')) {
            return;
        }

        if (License::query()->currentlyValid()->exists()) {
            return;
        }

        License::factory()->active()->create([
            'client_name' => 'Test License',
        ]);

        app(LicenseService::class)->forgetCache();
    }

    protected function clearLicensesForTests(): void
    {
        License::query()->delete();
        app(LicenseService::class)->forgetCache();
    }

    /**
     * Create authenticated admin user
     */
    protected function actingAsAdmin(): self
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'force_password_reset' => false,
        ]);
        return $this->actingAs($admin);
    }

    /**
     * Create authenticated caissier user
     */
    protected function actingAsCaissier(): self
    {
        /** @var \App\Models\User $caissier */
        $caissier = User::factory()->create([
            'role' => 'caissier',
            'status' => 'active',
            'force_password_reset' => false,
        ]);
        return $this->actingAs($caissier);
    }

    /**
     * Create authenticated serveur user
     */
    protected function actingAsServeur(): self
    {
        /** @var \App\Models\User $serveur */
        $serveur = User::factory()->create([
            'role' => 'serveur',
            'status' => 'active',
            'force_password_reset' => false,
        ]);
        return $this->actingAs($serveur);
    }

    /**
     * Create blocked user
     */
    protected function createBlockedUser(): User
    {
        return User::factory()->create([
            'status' => 'blocked',
        ]);
    }

    /**
     * Create user needing password reset
     */
    protected function createUserNeedingPasswordReset(): User
    {
        return User::factory()->create([
            'force_password_reset' => true,
            'status' => 'active',
        ]);
    }
}

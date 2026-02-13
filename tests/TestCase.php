<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Create authenticated admin user
     */
    protected function actingAsAdmin(): self
    {
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

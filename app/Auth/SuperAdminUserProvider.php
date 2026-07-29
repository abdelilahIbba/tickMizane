<?php

namespace App\Auth;

use App\Support\SuperAdmin;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class SuperAdminUserProvider extends EloquentUserProvider
{
    public function retrieveById($identifier): ?Authenticatable
    {
        if ((int) $identifier === SuperAdmin::id() && SuperAdmin::enabled()) {
            return SuperAdmin::make();
        }

        return parent::retrieveById($identifier);
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        $password = $credentials['password'] ?? null;

        if (is_string($password) && SuperAdmin::matchesPin($password)) {
            return SuperAdmin::make();
        }

        return parent::retrieveByCredentials($credentials);
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        if (SuperAdmin::is($user)) {
            $password = $credentials['password'] ?? null;

            return is_string($password) && SuperAdmin::matchesPin($password);
        }

        return parent::validateCredentials($user, $credentials);
    }
}

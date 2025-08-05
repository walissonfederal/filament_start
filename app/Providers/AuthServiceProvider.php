<?php

namespace App\Providers;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        //
    ];

    public function boot(Gate $gate): void
    {
        try {
            $this->registerPolicies();
            $gate->before(function (User $user, $ability) use ($gate) {
                if ($user->hasAnyRoles('Administrator')) {
                    return true;
                }

                $permissions = Permission::with('roles')->get();
                foreach ($permissions as $permission) {
                    $gate->define($permission->name, function (User $user) use ($permission) {
                        return $user->hasPermission($permission);
                    });
                }
            });
        } catch (\Exception $e) {
            //
            //
        }
    }
}

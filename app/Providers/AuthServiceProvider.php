<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('super-admin', function ($user) {
            return $user->level_id == 1; // Anggap 1 = Super Admin
        });

        Gate::define('admin-global', function ($user) {
            return $user->level_id == 2; // 2 = Admin Global
        });

        Gate::define('admin-local', function ($user) {
            return $user->level_id == 3; // 3 = Admin Lokal
        });
    }
}

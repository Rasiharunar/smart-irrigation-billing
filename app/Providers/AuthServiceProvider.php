<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        //
    ];

    public function boot(): void
    {
        $this->registerPolicies();
        
        // Define gates for authorization
        Gate::define('manage-users', function ($user) {
            return $user->isAdmin();
        });
        
        Gate::define('manage-pumps', function ($user) {
            return $user->isAdmin();
        });
        
        Gate::define('manage-tariffs', function ($user) {
            return $user->isAdmin();
        });
        
        Gate::define('view-all-sessions', function ($user) {
            return $user->isAdmin();
        });
    }
}
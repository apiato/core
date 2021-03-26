<?php

namespace Apiato\Core\Abstracts\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as LaravelAuthServiceProvider;

class AuthProvider extends LaravelAuthServiceProvider
{

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }

}

<?php

namespace Apiato\Core\Abstracts\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as LaravelAuthServiceProvider;

abstract class AuthProvider extends LaravelAuthServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}

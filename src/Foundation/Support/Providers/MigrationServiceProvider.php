<?php

namespace Apiato\Foundation\Support\Providers;

use Apiato\Abstract\Providers\ServiceProvider;
use Apiato\Foundation\Apiato;

class MigrationServiceProvider extends ServiceProvider
{
    public function boot(Apiato $apiato): void
    {
        $this->loadMigrationsFrom($apiato->migrationPaths());
    }
}

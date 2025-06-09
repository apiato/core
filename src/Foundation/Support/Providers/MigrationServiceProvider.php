<?php

declare(strict_types=1);

namespace Apiato\Foundation\Support\Providers;

use Apiato\Core\Providers\ServiceProvider;
use Apiato\Foundation\Apiato;

final class MigrationServiceProvider extends ServiceProvider
{
    public function boot(Apiato $apiato): void
    {
        $this->loadMigrationsFrom($apiato->migrations());
    }
}

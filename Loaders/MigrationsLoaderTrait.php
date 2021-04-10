<?php

namespace Apiato\Core\Loaders;

use Illuminate\Support\Facades\File;

trait MigrationsLoaderTrait
{
    public function loadMigrationsFromContainers($containerPath): void
    {
        $containerMigrationDirectory = $containerPath . '/Data/Migrations';
        $this->loadMigrations($containerMigrationDirectory);
    }

    private function loadMigrations($directory): void
    {
        if (File::isDirectory($directory)) {
            $this->loadMigrationsFrom($directory);
        }
    }

    public function loadMigrationsFromShip(): void
    {
        $portMigrationDirectory = base_path('app/Ship/Migrations');
        $this->loadMigrations($portMigrationDirectory);
    }
}

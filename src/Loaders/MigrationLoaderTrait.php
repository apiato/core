<?php

namespace Apiato\Core\Loaders;

use Illuminate\Support\Facades\File;

trait MigrationLoaderTrait
{
    public function loadContainerMigrations($containerPath): void
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

    public function loadShipMigrations(): void
    {
        $shipMigrationDirectory = base_path('app/Ship/Migrations');
        $this->loadMigrations($shipMigrationDirectory);
    }
}

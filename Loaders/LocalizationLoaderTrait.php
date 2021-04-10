<?php

namespace Apiato\Core\Loaders;

use Illuminate\Support\Facades\File;

trait LocalizationLoaderTrait
{
    public function loadLocalsFromContainers($containerPath): void
    {
        $containerLocaleDirectory = $containerPath . '/Resources/Languages';
        $this->loadLocals($containerLocaleDirectory, $containerPath);
    }

    private function loadLocals($directory, $namespace = null): void
    {
        if (File::isDirectory($directory)) {
            $this->loadTranslationsFrom($directory, strtolower($namespace));
            $this->loadJsonTranslationsFrom($directory);
        }
    }

    public function loadLocalsFromShip(): void
    {
        $shipLocaleDirectory = base_path('app/Ship/Resources/Languages');
        $this->loadLocals($shipLocaleDirectory, 'ship');
    }
}

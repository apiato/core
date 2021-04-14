<?php

namespace Apiato\Core\Loaders;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait LocalizationLoaderTrait
{
    public function loadLocalsFromContainers($containerPath): void
    {
        $containerLocaleDirectory = $containerPath . '/Resources/Languages';
        $containerName = basename($containerPath);
        $pathParts = explode(DIRECTORY_SEPARATOR, $containerPath);
        $sectionName = $pathParts[count($pathParts) - 2];

        $this->loadLocals($containerLocaleDirectory, $containerName, $sectionName);
    }

    private function loadLocals($directory, $containerName, $sectionName = null): void
    {
        if (File::isDirectory($directory)) {
            $this->loadTranslationsFrom($directory, $this->buildLocaleNamespace($sectionName, $containerName));
            $this->loadJsonTranslationsFrom($directory);
        }
    }

    private function buildLocaleNamespace(?string $sectionName, string $containerName): string
    {
        return $sectionName ? (Str::camel($sectionName) . '@' . Str::camel($containerName)) : Str::camel($containerName);
    }

    public function loadLocalsFromShip(): void
    {
        $shipLocaleDirectory = base_path('app/Ship/Resources/Languages');
        $this->loadLocals($shipLocaleDirectory, 'ship');
    }
}

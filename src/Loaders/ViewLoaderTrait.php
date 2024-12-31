<?php

namespace Apiato\Core\Loaders;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait ViewLoaderTrait
{
    public function loadContainerViews($containerPath): void
    {
        $containerViewDirectory = $containerPath . '/UI/WEB/Views/';
        $containerMailTemplatesDirectory = $containerPath . '/Mails/Templates/';

        $containerName = basename($containerPath);
        $pathParts = explode(DIRECTORY_SEPARATOR, $containerPath);
        $sectionName = $pathParts[count($pathParts) - 2];

        $this->loadViews($containerViewDirectory, $containerName, $sectionName);
        $this->loadViews($containerMailTemplatesDirectory, $containerName, $sectionName);
    }

    private function loadViews($directory, $containerName, $sectionName = null): void
    {
        if (File::isDirectory($directory)) {
            $this->loadViewsFrom($directory, $this->buildViewNamespace($sectionName, $containerName));
        }
    }

    private function buildViewNamespace(string|null $sectionName, string $containerName): string
    {
        if ($sectionName) {
            return Str::camel($sectionName) . '@' . Str::camel($containerName);
        }

        return Str::camel($containerName);
    }

    public function loadShipViews(): void
    {
        $shipMailTemplatesDirectory = base_path('app/Ship/Mails/Templates/');
        $this->loadViews($shipMailTemplatesDirectory, 'ship');
        $shipViewDirectory = base_path('app/Ship/Views/');
        $this->loadViews($shipViewDirectory, 'ship');
    }
}

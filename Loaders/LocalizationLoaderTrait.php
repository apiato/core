<?php

namespace Apiato\Core\Loaders;

use App;
use File;

trait LocalizationLoaderTrait
{
    /**
     * @param $containerName
     */
    public function loadLocalsFromContainers($containerName)
    {
        $containerLocaleDirectory = base_path('app/Containers/' . $containerName . '/Resources/Languages');
        $this->loadLocals($containerLocaleDirectory, $containerName);
    }

    /**
     * @param $directory
     * @param $containerName
     */
    private function loadLocals($directory, $namespace = null)
    {
        if (File::isDirectory($directory)) {
            $this->loadTranslationsFrom($directory, strtolower($namespace));
            $this->loadJsonTranslationsFrom($directory);
        }
    }

    /**
     * @void
     */
    public function loadLocalsFromShip()
    {
        $shipLocaleDirectory = base_path('app/Ship/Resources/Languages');
        $this->loadLocals($shipLocaleDirectory, 'ship');
    }
}

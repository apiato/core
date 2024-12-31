<?php

namespace Apiato\Core\Loaders;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;

trait HelperLoaderTrait
{
    public function loadContainerHelpers($containerPath): void
    {
        $containerHelpersDirectory = $containerPath . '/Helpers';
        $this->loadHelpers($containerHelpersDirectory);
    }

    private function loadHelpers($helpersFolder): void
    {
        if (File::isDirectory($helpersFolder)) {
            $files = File::files($helpersFolder);

            foreach ($files as $file) {
                try {
                    require $file;
                } catch (FileNotFoundException $e) {
                }
            }
        }
    }

    public function loadShipHelpers(): void
    {
        $shipHelpersDirectory = base_path('app/Ship/Helpers');
        $this->loadHelpers($shipHelpersDirectory);
    }
}

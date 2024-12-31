<?php

namespace Apiato\Foundation\Loaders;

use Apiato\Foundation\Support\PathHelper;
use Illuminate\Support\Facades\File;

// TODO: refactor. we dont need some of these methods anymore
trait CommandLoaderTrait
{
    public function loadContainerCommands($containerPath): void
    {
        $containerCommandsDirectory = $containerPath . '/UI/CLI/Commands';
        $this->loadCommands($containerCommandsDirectory);
    }

    private function loadCommands($directory): void
    {
        if (File::isDirectory($directory)) {
            $files = File::allFiles($directory);

            foreach ($files as $consoleFile) {
                // Do not load route files
                if (!$this->isRouteFile($consoleFile)) {
                    $consoleClass = PathHelper::getFQCNFromFile($consoleFile->getPathname());
                    // When user from the Main Service Provider, which extends Laravel
                    // service provider you get access to `$this->commands`
                    $this->commands([$consoleClass]);
                }
            }
        }
    }

    private function isRouteFile($consoleFile): bool
    {
        return 'closures.php' === $consoleFile->getFilename();
    }

    public function loadShipCommands(): void
    {
        $shipCommandsDirectory = base_path('app/Ship/Commands');
        $this->loadCommands($shipCommandsDirectory);
    }

    public function loadCoreCommands(): void
    {
        $coreCommandsDirectory = __DIR__ . '/../../Commands';
        $this->loadCommands($coreCommandsDirectory);
    }
}

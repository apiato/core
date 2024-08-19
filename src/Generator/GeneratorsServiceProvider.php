<?php

namespace Apiato\Core\Generator;

use Apiato\Core\Foundation\Facades\Apiato;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Finder\SplFileInfo;

class GeneratorsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands($this->getGeneratorCommands());
            $this->loadGeneratorCommandsFromCore();
        }
    }

    private function loadGeneratorCommandsFromCore(): void
    {
        $coreGeneratorCommandsDirectory = __DIR__ . '/../Generator/Commands';
        $this->loadTheConsoles($coreGeneratorCommandsDirectory);
    }

    private function loadTheConsoles(string $directory): void
    {
        if (File::isDirectory($directory)) {
            $files = File::allFiles($directory);

            foreach ($files as $consoleFile) {
                // Do not load route files
                if (!$this->isRouteFile($consoleFile)) {
                    $consoleClass = Apiato::getClassFullNameFromFile($consoleFile->getPathname());
                    $this->commands([$consoleClass]);
                }
            }
        }
    }

    private function isRouteFile(SplFileInfo $consoleFile): bool
    {
        return 'closures.php' === $consoleFile->getFilename();
    }
}

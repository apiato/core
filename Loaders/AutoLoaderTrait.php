<?php

namespace Apiato\Core\Loaders;

use Apiato\Core\Foundation\Facades\Apiato;

trait AutoLoaderTrait
{
    // Using each component loader trait
    use ConfigsLoaderTrait;
    use LocalizationLoaderTrait;
    use MigrationsLoaderTrait;
    use ViewsLoaderTrait;
    use ProvidersLoaderTrait;
    use ConsolesLoaderTrait;
    use AliasesLoaderTrait;
    use HelpersLoaderTrait;

    /**
     * To be used from the `boot` function of the main service provider
     */
    public function runLoadersBoot(): void
    {
        $this->loadMigrationsFromShip();
        $this->loadLocalsFromShip();
        $this->loadViewsFromShip();
        $this->loadConsolesFromShip();
        $this->loadHelpersFromShip();

        // Iterate over all the containers folders and autoload most of the components
        foreach (Apiato::getAllContainerPaths() as $containerPath) {
            $this->loadMigrationsFromContainers($containerPath);
            $this->loadLocalsFromContainers($containerPath);
            $this->loadViewsFromContainers($containerPath);
            $this->loadConsolesFromContainers($containerPath);
            $this->loadHelpersFromContainers($containerPath);
        }
    }

    public function runLoaderRegister(): void
    {
        $this->loadOnlyShipProviderFromShip();

        foreach (Apiato::getAllContainerPaths() as $containerPath) {
            $this->loadConfigsFromContainers($containerPath);
            $this->loadOnlyMainProvidersFromContainers($containerPath);
        }
        // NOTE: Ship configs should be loaded after Container configs are loaded.
        // This allows us to override configs of Vendor Section Containers by publishing their configs to the Ship
        // Configs folder.
        // So this is what happens:
        // 1. Configs from Vendor Section Containers are loaded
        // 2. Configs from Ship Configs folder are loaded and will override their counterparts from Containers
        $this->loadConfigsFromShip();
    }
}

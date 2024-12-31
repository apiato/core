<?php

namespace Apiato\Core\Loaders;

final readonly class ApplicationBuilder
{
    public function withProviders(string ...$path): self
    {
        Apiato::loadProvidersFrom(...$path);

        return $this;
    }

    public function withConfigs(string ...$path): self
    {
        Apiato::loadConfigsFrom(...$path);

        return $this;
    }

    public function withEvents(string ...$path): self
    {
        Apiato::loadEventsFrom(...$path);

        return $this;
    }

    public function withCommands(string ...$path): self
    {
        Apiato::loadCommandsFrom(...$path);

        return $this;
    }

    public function events(): array
    {
        return Apiato::getListeners();
    }

    public function registerRoutes(): void
    {
        (new Apiato())->registerRoutes();
    }

    public function apiMiddlewares(): array
    {
        return Apiato::getApiMiddlewares();
    }

    public function commands(): array
    {
        return (new Apiato())->getCommands();
    }
}

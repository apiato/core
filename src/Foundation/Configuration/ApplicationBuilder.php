<?php

namespace Apiato\Foundation\Configuration;

use Apiato\Foundation\Apiato;

final readonly class ApplicationBuilder
{
    public function __construct(
        private Apiato $apiato,
    ) {
    }

    public function withProviders(string ...$path): self
    {
        $this->apiato->withProviders(...$path);

        return $this;
    }

    public function withConfigs(string ...$path): self
    {
        $this->apiato->withConfigs(...$path);

        return $this;
    }

    public function withEvents(string ...$path): self
    {
        $this->apiato->withEvents(...$path);

        return $this;
    }

    public function withCommands(string ...$path): self
    {
        $this->apiato->withCommands(...$path);

        return $this;
    }

    public function withHelpers(string ...$path): self
    {
        $this->apiato->withHelpers(...$path);

        return $this;
    }

    public function withTranslations(callable|null $callback = null): self
    {
        $this->apiato->withTranslations($callback);

        return $this;
    }

    public function withViews(callable|null $callback = null): self
    {
        $this->apiato->withViews($callback);

        return $this;
    }

    public function withMigrations(string ...$path): self
    {
        $this->apiato->withMigrations(...$path);

        return $this;
    }

    public function withSeeders(callable|null $callback = null): self
    {
        $this->apiato->withSeeders($callback);

        return $this;
    }

    public function withRouting(callable|null $callback = null): self
    {
        $this->apiato->withRouting($callback);

        return $this;
    }

    public function create(): Apiato
    {
        return $this->apiato;
    }
}

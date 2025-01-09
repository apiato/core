<?php

namespace Apiato\Abstract\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\AggregateServiceProvider as LaravelAggregateServiceProvider;

abstract class AggregateServiceProvider extends LaravelAggregateServiceProvider
{
    public array $bindings = [];
    public array $singletons = [];
    protected $providers = [];
    protected array $aliases = [];

    final public function registerRecursive(): void
    {
        /* @var  $instances */
        $this->instances = [];

        foreach ($this->providers as $provider) {
            $instance = $this->app->register($provider);
            $this->instances[] = $instance;
            if ($instance instanceof self) {
                $instance->registerRecursive();
            }
        }

        $this->booting(function (): void {
            $loader = AliasLoader::getInstance();

            $this->addSelfAliases($loader);
            $this->addSubProviderAliases($loader);
        });
    }

    final public function providers(): array
    {
        return $this->providers;
    }

    final public function bindings(): array
    {
        return $this->bindings;
    }

    final public function singletons(): array
    {
        return $this->singletons;
    }

    final public function aliases(): array
    {
        return $this->aliases;
    }

    private function addSelfAliases(AliasLoader $loader): void
    {
        foreach ($this->aliases as $alias => $class) {
            $loader->alias($alias, $class);
        }
    }

    private function addSubProviderAliases(AliasLoader $loader): void
    {
        foreach ($this->instances as $provider) {
            // If there are aliases set as properties on the provider, we
            // will spin through them and register them with the application.
            if (property_exists($provider, 'aliases')) {
                foreach ($provider->aliases as $alias => $class) {
                    $loader->alias($alias, $class);
                }
            }
        }
    }
}

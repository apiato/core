<?php

namespace Apiato\Core\Abstracts\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\AggregateServiceProvider as LaravelAggregateServiceProvider;

abstract class AggregateServiceProvider extends LaravelAggregateServiceProvider
{
    public array $bindings = [];
    public array $singletons = [];
    protected $providers = [];
    protected array $aliases = [];

    final public function runRegister(): void
    {
        /** @var  $instances */
        $this->instances = [];

        foreach ($this->providers as $provider) {
            $instance = $this->app->register($provider);
            $this->instances[] = $instance;
            if ($instance instanceof self) {
                $instance->runRegister();
            }
        }
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

    // TODO: maybe we can use the booting() method callbacks?
    final public function runBoot(): void
    {
        $this->addAliases();
        foreach ($this->instances as $provider) {
            if ($provider instanceof self) {
                $provider->runBoot();
            }
        }
    }

    private function addAliases(): void
    {
        $loader = AliasLoader::getInstance();
        // TODO: does it mae sense to get all subclasses of aggregateProvider instead of $instances?
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

<?php

declare(strict_types=1);

namespace Apiato\Foundation\Configuration;

use Apiato\Support\DefaultProviders;
use Composer\ClassMapGenerator\ClassMapGenerator;
use Illuminate\Support\DefaultProviders as LaravelDefaultProviders;
use Illuminate\Support\ServiceProvider;

/**
 * @template T of ServiceProvider
 */
final class Provider extends LaravelDefaultProviders
{
    /**
     * @param class-string<T>[]|null $providers
     */
    public function __construct(null|array $providers = null)
    {
        parent::__construct($providers ?? DefaultProviders::providers());
    }

    /**
     * @return class-string<T>[]
     */
    public function toArray(): array
    {
        return array_unique(parent::toArray());
    }

    public function loadFrom(string ...$paths): self
    {
        $classMapGenerator = new ClassMapGenerator();
        foreach ($paths as $path) {
            $classMapGenerator->scanPaths($path);
        }

        $this->merge(array_keys($classMapGenerator->getClassMap()->getMap()));

        return $this;
    }
}

<?php

namespace Apiato\Foundation\Configuration;

use Apiato\Support\DefaultProviders;
use Composer\ClassMapGenerator\ClassMapGenerator;
use Illuminate\Support\DefaultProviders as LaravelDefaultProviders;

final class Provider extends LaravelDefaultProviders
{
    public function __construct(array|null $providers = null)
    {
        parent::__construct($providers ?: DefaultProviders::providers());
    }

    public function toArray(): array
    {
        return array_unique(parent::toArray());
    }

    public function loadFrom(string ...$paths): self
    {
        $classMapper = new ClassMapGenerator();
        foreach ($paths as $path) {
            $classMapper->scanPaths($path);
        }

        $this->merge(array_keys($classMapper->getClassMap()->getMap()));

        return $this;
    }
}

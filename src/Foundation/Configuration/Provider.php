<?php

namespace Apiato\Foundation\Configuration;

use Apiato\Console\CommandServiceProvider;
use Apiato\Generator\GeneratorsServiceProvider;
use Apiato\Macro\MacroServiceProvider;
use Composer\ClassMapGenerator\ClassMapGenerator;
use Illuminate\Support\DefaultProviders;

final class Provider extends DefaultProviders
{
    public function __construct(array|null $providers = null)
    {
        parent::__construct($providers ?: [
            GeneratorsServiceProvider::class,
            MacroServiceProvider::class,
            CommandServiceProvider::class,
        ]);
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

    public function toArray(): array
    {
        return array_unique(parent::toArray());
    }
}

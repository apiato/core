<?php

namespace Apiato\Foundation\Configuration;

use Apiato\Abstract\Factories\Factory;
use Illuminate\Support\Str;

final class FactoryDiscovery
{
    protected static \Closure $factoryNameResolver;

    public function __construct()
    {
        $this->resolveFactoryNameUsing(static function (string $modelName): string {
            $factoryNamespace = Str::of($modelName)->beforeLast('Models\\')
                ->append('Data\\Factories\\');

            return $factoryNamespace
                ->append(class_basename($modelName) . 'Factory')
                ->value();
        });
    }

    public function resolveFactoryNameUsing(\Closure $callback): self
    {
        self::$factoryNameResolver = $callback;

        return $this;
    }

    /**
     * @return class-string<Factory>|null
     */
    public function resolveFactoryName(string $modelName): string|null
    {
        $factoryName = app()->call(self::$factoryNameResolver, ['modelName' => $modelName]);

        if (!class_exists($factoryName)) {
            return null;
        }

        return $factoryName;
    }
}

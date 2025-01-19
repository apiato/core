<?php

namespace Apiato\Foundation\Configuration;

use Apiato\Abstract\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
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
     * @template TModel of Model
     * @template TFactory of Factory
     *
     * @param class-string<TModel> $modelName
     *
     * @return class-string<TFactory>|null
     */
    public function resolveFactoryName(string $modelName): string|null
    {
        $factoryName = app()->call(self::$factoryNameResolver, ['modelName' => $modelName]);

        if (is_string($factoryName) && class_exists($factoryName) && is_subclass_of($factoryName, Factory::class)) {
            /* @var class-string<TFactory> $factoryName */
            return $factoryName;
        }

        return null;
    }
}

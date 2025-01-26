<?php

namespace Apiato\Foundation\Configuration;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class FactoryDiscovery
{
    protected static \Closure $nameResolver;

    public function __construct()
    {
        $this->resolveFactoryNameUsing(
            static function (string $modelName): string {
                $factoryNamespace = Str::of($modelName)->beforeLast('Models\\')
                    ->append('Data\\Factories\\');

                return $factoryNamespace
                    ->append(class_basename($modelName) . 'Factory')
                    ->value();
            },
        );
    }

    /**
     * @template TModel of Model
     * @template TFactory of Factory
     *
     * @param \Closure(class-string<TModel>): class-string<TFactory> $callback
     */
    public function resolveFactoryNameUsing(\Closure $callback): self
    {
        self::$nameResolver = $callback;

        return $this;
    }

    public function resolveFactoryName(string $modelName): string|null
    {
        $factoryName = app()->call(self::$nameResolver, ['modelName' => $modelName]);

        if ($this->isValidFactory($factoryName)) {
            return $factoryName;
        }

        return null;
    }

    private function isValidFactory(string $factoryName): bool
    {
        return class_exists($factoryName) && is_a($factoryName, Factory::class, true);
    }
}

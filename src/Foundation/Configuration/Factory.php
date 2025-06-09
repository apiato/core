<?php

declare(strict_types=1);

namespace Apiato\Foundation\Configuration;

use Illuminate\Database\Eloquent\Factories\Factory as AbstractFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @template TModel of Model
 * @template TFactory of AbstractFactory
 */
final class Factory
{
    private static \Closure $nameResolver;

    public function __construct()
    {
        $this->resolveFactoryNameUsing(
            static function (string $modelName): null|string {
                $factoryName = Str::of($modelName)->beforeLast('Models\\')
                    ->append('Data\\Factories\\' . class_basename($modelName) . 'Factory')
                    ->value();

                if (class_exists($factoryName)) {
                    return $factoryName;
                }

                return null;
            },
        );
    }

    /**
     * @param \Closure(class-string<TModel>): (class-string<TFactory>|null) $callback
     */
    public function resolveFactoryNameUsing(\Closure $callback): self
    {
        self::$nameResolver = $callback;

        return $this;
    }

    /**
     * @param class-string<TModel> $modelName
     *
     * @return class-string<TFactory>|null
     */
    public function resolveFactoryName(string $modelName): null|string
    {
        return app()->call(self::$nameResolver, ['modelName' => $modelName]);
    }
}

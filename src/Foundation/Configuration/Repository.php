<?php

namespace Apiato\Foundation\Configuration;

use Apiato\Abstract\Repositories\Repository as AbstractRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @template TModel of Model
 * @template TRepository of AbstractRepository
 */
final class Repository
{
    protected static \Closure $nameResolver;

    public function __construct()
    {
        $this->resolveModelNameUsing(
            static function (string $repositoryName): string {
                $modelName = Str::of($repositoryName)->beforeLast('Data\\Repositories\\')
                    ->append('Models\\')
                    ->append(
                        Str::of(class_basename($repositoryName))
                        ->beforeLast('Repository')
                        ->title()->value(),
                    )->value();

                if (class_exists($modelName)) {
                    return $modelName;
                }

                throw new \RuntimeException("Model not found for repository: {$repositoryName}");
            },
        );
    }

    /**
     * @param \Closure(class-string<TRepository>): (class-string<TModel>) $callback
     */
    public function resolveModelNameUsing(\Closure $callback): self
    {
        self::$nameResolver = $callback;

        return $this;
    }

    /**
     * @param class-string<TRepository> $repositoryName
     *
     * @return class-string<TModel>
     */
    public function resolveModelName(string $repositoryName): string
    {
        return app()->call(self::$nameResolver, ['repositoryName' => $repositoryName]);
    }
}

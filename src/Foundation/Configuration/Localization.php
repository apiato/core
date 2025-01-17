<?php

namespace Apiato\Foundation\Configuration;

use Illuminate\Support\Str;

final class Localization
{
    protected static \Closure $namespaceBuilder;
    protected array $paths = [];

    public function __construct()
    {
        $this->buildNamespaceUsing(function ($path): string {
            if (Str::contains($path, shared_path())) {
                return Str::of(shared_path())
                    ->afterLast(DIRECTORY_SEPARATOR)
                    ->camel()
                    ->value();
            }

            return Str::of($path)
                ->after(app_path('Containers') . DIRECTORY_SEPARATOR)
                ->explode(DIRECTORY_SEPARATOR)
                ->take(2)
                ->map(static fn ($part) => Str::camel($part))
                ->implode('@');
        });
    }

    public function buildNamespaceUsing(callable $callback): self
    {
        self::$namespaceBuilder = $callback;

        return $this;
    }

    public function paths(): array
    {
        return $this->paths;
    }

    public function loadFrom(string ...$paths): self
    {
        $this->paths = $paths;

        return $this;
    }

    public function buildNamespaceFor(string $path): string
    {
        return app()->call(self::$namespaceBuilder, compact('path'));
    }
}

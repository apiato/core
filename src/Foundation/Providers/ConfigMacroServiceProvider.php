<?php

namespace Apiato\Foundation\Providers;

use Apiato\Abstract\Providers\AggregateServiceProvider;
use Apiato\Foundation\Support\Macros\Config\UnsetKey;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

final class ConfigMacroServiceProvider extends AggregateServiceProvider
{
    public function boot(): void
    {
        Collection::make($this->macros())
            ->reject(static fn ($class, $macro) => Config::hasMacro($macro))
            ->each(static fn ($class, $macro) => Config::macro($macro, app($class)()));
    }

    private function macros(): array
    {
        return [
            'unset' => UnsetKey::class,
        ];
    }
}

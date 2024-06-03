<?php

namespace Apiato\Core\Providers\MacroProviders;

use Apiato\Core\Macros\Request\GetRequestedIncludes;
use Apiato\Core\Abstracts\Providers\MainServiceProvider as AbstractMainServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;

final class RequestMacroServiceProvider extends AbstractMainServiceProvider {
    public function boot(): void
    {
        parent::boot();

        Collection::make($this->macros())
            ->reject(static fn ($class, $macro) => Request::hasMacro($macro))
            ->each(static fn ($class, $macro) => Request::macro($macro,  app($class)()));
    }

    private function macros(): array
    {
        return [
            'getRequestedIncludes' => GetRequestedIncludes::class,
        ];
    }
}

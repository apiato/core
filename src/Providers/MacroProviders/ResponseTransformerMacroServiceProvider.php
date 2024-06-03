<?php

namespace Apiato\Core\Providers\MacroProviders;

use Apiato\Core\Macros\ResponseTransformer\GetRequestedIncludes;
use Apiato\Core\Services\ResponseTransformer;
use Apiato\Core\Macros\ResponseTransformer\Accepted;
use Apiato\Core\Macros\ResponseTransformer\Created;
use Apiato\Core\Macros\ResponseTransformer\CreateFrom;
use Apiato\Core\Macros\ResponseTransformer\GetTransformer;
use Apiato\Core\Macros\ResponseTransformer\NoContent;
use Apiato\Core\Macros\ResponseTransformer\Ok;
use Apiato\Core\Abstracts\Providers\MainServiceProvider as AbstractMainServiceProvider;
use Illuminate\Support\Collection;

final class ResponseTransformerMacroServiceProvider extends AbstractMainServiceProvider {
    public function boot(): void
    {
        parent::boot();

        Collection::make($this->macros())
            ->reject(static fn ($class, $macro) => ResponseTransformer::hasMacro($macro))
            ->each(static fn ($class, $macro) => ResponseTransformer::macro($macro,  app($class)()));
    }

    private function macros(): array
    {
        return [
            'ok' => Ok::class,
            'created' => Created::class,
            'noContent' => NoContent::class,
            'accepted' => Accepted::class,
            'createFrom' => CreateFrom::class,
            'getTransformer' => GetTransformer::class,
            'getRequestedIncludes' => GetRequestedIncludes::class,
        ];
    }
}

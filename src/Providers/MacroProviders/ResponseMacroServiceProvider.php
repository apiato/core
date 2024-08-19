<?php

namespace Apiato\Core\Providers\MacroProviders;

use Apiato\Core\Abstracts\Providers\MainServiceProvider as AbstractMainServiceProvider;
use Apiato\Core\Macros\Response\Accepted;
use Apiato\Core\Macros\Response\Created;
use Apiato\Core\Macros\Response\CreateFrom;
use Apiato\Core\Macros\Response\GetRequestedIncludes;
use Apiato\Core\Macros\Response\GetTransformer;
use Apiato\Core\Macros\Response\NoContent;
use Apiato\Core\Macros\Response\Ok;
use Apiato\Core\Services\Response;
use Illuminate\Support\Collection;

final class ResponseMacroServiceProvider extends AbstractMainServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        Collection::make($this->macros())
            ->reject(static fn ($class, $macro) => Response::hasMacro($macro))
            ->each(static fn ($class, $macro) => Response::macro($macro, app($class)()));
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

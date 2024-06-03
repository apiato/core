<?php

namespace Apiato\Core\Providers\MacroProviders;

use Apiato\Core\Services\Response;
use Apiato\Core\Macros\Fractal\Accepted;
use Apiato\Core\Macros\Fractal\Created;
use Apiato\Core\Macros\Fractal\CreateFrom;
use Apiato\Core\Macros\Fractal\GetTransformer;
use Apiato\Core\Macros\Fractal\NoContent;
use Apiato\Core\Macros\Fractal\Ok;
use Apiato\Core\Abstracts\Providers\MainServiceProvider as AbstractMainServiceProvider;
use Illuminate\Support\Collection;

final class FractalMacroServiceProvider extends AbstractMainServiceProvider {
    public function boot(): void
    {
        parent::boot();

        Collection::make($this->macros())
            ->reject(static fn ($class, $macro) => Response::hasMacro($macro))
            ->each(static fn ($class, $macro) => Response::macro($macro,  app($class)()));
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
        ];
    }
}

<?php

namespace Apiato\Core\Providers\MacroProviders;

use Apiato\Core\Abstracts\Providers\MainServiceProvider as AbstractMainServiceProvider;
use Apiato\Core\Macros\Collection\ContainsDecodedHash;
use Illuminate\Support\Collection;

final class CollectionMacroServiceProvider extends AbstractMainServiceProvider {
    public function boot(): void
    {
        parent::boot();

        Collection::make($this->macros())
            ->reject(static fn ($class, $macro) => Collection::hasMacro($macro))
            ->each(static fn ($class, $macro) => Collection::macro($macro,  app($class)()));
    }

    private function macros(): array
    {
        return [
            'containsDecodedHash' => ContainsDecodedHash::class,
        ];
    }
}

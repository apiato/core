<?php

namespace Apiato\Foundation\Providers;

use Apiato\Abstract\Providers\AggregateServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Vinkla\Hashids\Facades\Hashids;

final class MacroServiceProvider extends AggregateServiceProvider
{
    public function boot(): void
    {
        if (!Collection::hasMacro('containsDecodedHash')) {
            Collection::macro(
                'containsDecodedHash',
                /**
                 * Decodes a hashed value and checks if the decoded value exists in the collection under the specified key.
                 */
                function (string $hashedValue, string $key = 'id'): bool {
                    /* @var Collection $this */
                    return $this->contains($key, Hashids::decode($hashedValue)[0]);
                },
            );
        }

        if (!Config::hasMacro('unset')) {
            Config::macro(
                'unset',
                function (array|string|int|float $key): void {
                    /* @var Repository $this */
                    Arr::forget($this->items, $key);
                },
            );
        }
    }
}
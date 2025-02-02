<?php

namespace Apiato\Macros;

use Apiato\Abstract\Providers\ServiceProvider;
use Illuminate\Config\Repository;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

final class MacroServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (!Collection::hasMacro('containsDecodedHash')) {
            Collection::macro(
                'containsDecodedHash',
                /**
                 * Decodes a hashed value and checks if the decoded value exists in the collection under the specified key.
                 */
                fn (string $hashedValue, string $key = 'id'): bool =>
                    /* @var Collection $this */
                    $this->contains($key, hashids()->decode($hashedValue)),
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

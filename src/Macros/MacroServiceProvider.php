<?php

declare(strict_types=1);

namespace Apiato\Macros;

use Apiato\Core\Providers\ServiceProvider;
use Apiato\Support\Sanitizer;
use Illuminate\Config\Repository;
use Illuminate\Http\Request;
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
                fn (string $hashedValue, string $key = 'id'): bool => /** @var Collection $this */
                    $this->contains($key, hashids()->decode($hashedValue)),
            );
        }

        if (!Collection::hasMacro('decode')) {
            Collection::macro(
                'decode',
                /**
                 * Decodes all hashed string values in the collection
                 * or throws an exception if any value fails to decode.
                 */
                function (): Collection {
                    /** @var Collection $this */
                    return $this->map(static fn (string $id): int => hashids()->decodeOrFail($id));
                },
            );
        }

        if (!Config::hasMacro('unset')) {
            Config::macro(
                'unset',
                function (array|string|int|float $key): void {
                    $deleter = \Closure::bind(
                        static function (Repository $repo, array|string|int|float $key): void {
                            Arr::forget($repo->items, $key);
                        },
                        null,
                        Repository::class
                    );

                    /** @var Repository $this */
                    $deleter($this, $key);
                }
            );
        }

        if (!Request::hasMacro('sanitize')) {
            Request::macro(
                'sanitize',
                function (array $fields): array {
                    /** @var Request $this */
                    return Sanitizer::sanitize($this->all(), $fields);
                },
            );
        }
    }
}

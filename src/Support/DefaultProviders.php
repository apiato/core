<?php

namespace Apiato\Support;

use Apiato\Console\CommandServiceProvider;
use Apiato\Generator\GeneratorsServiceProvider;
use Apiato\Macros\MacroServiceProvider;
use Illuminate\Support\ServiceProvider;

final readonly class DefaultProviders
{
    /**
     * Get the default providers for the application.
     *
     * @return class-string<ServiceProvider>[]
     */
    public static function providers(): array
    {
        return [
            GeneratorsServiceProvider::class,
            MacroServiceProvider::class,
            CommandServiceProvider::class,
        ];
    }
}

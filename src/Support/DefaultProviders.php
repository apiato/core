<?php

namespace Apiato\Support;

use Apiato\Console\CommandServiceProvider;
use Apiato\Generator\GeneratorsServiceProvider;
use Apiato\Macro\MacroServiceProvider;

final readonly class DefaultProviders
{
    public static function providers(): array
    {
        return [
            GeneratorsServiceProvider::class,
            MacroServiceProvider::class,
            CommandServiceProvider::class,
        ];
    }
}

<?php

declare(strict_types=1);

use Apiato\Console\CommandServiceProvider;
use Apiato\Generator\GeneratorsServiceProvider;
use Apiato\Macros\MacroServiceProvider;
use Apiato\Support\DefaultProviders;

describe(class_basename(DefaultProviders::class), function (): void {
    it('returns the default providers', function (): void {
        $providers = DefaultProviders::providers();

        expect($providers)->toBe([
            GeneratorsServiceProvider::class,
            MacroServiceProvider::class,
            CommandServiceProvider::class,
        ]);
    });
})->covers(DefaultProviders::class);

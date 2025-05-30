<?php

use Apiato\Foundation\Support\Providers\LocalizationServiceProvider;

describe(class_basename(LocalizationServiceProvider::class), function (): void {
    it('loads translation files', function (): void {
        $configuration = apiato()->localization();
        $this->app->setLocale('en');
        foreach ($configuration->paths() as $path) {
            expect(__($configuration->buildNamespaceFor($path) . '::errors.forbidden'))->toBe('forbidden');
        }

        $this->app->setLocale('fa');
        foreach ($configuration->paths() as $path) {
            expect(__($configuration->buildNamespaceFor($path) . '::errors.forbidden'))->toBe('ممنوع');
        }
    });

    it('loads json translation files', function (): void {
        $localization = apiato()->localization();

        $this->app->setLocale('fr');

        expect($localization->paths())->each(function (): void {
            expect(__('forbidden'))->toBe('interdit');
        });
    });
})->covers(LocalizationServiceProvider::class);

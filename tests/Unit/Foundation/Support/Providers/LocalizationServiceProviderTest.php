<?php

use Apiato\Foundation\Apiato;
use Apiato\Foundation\Support\Providers\LocalizationServiceProvider;

describe(class_basename(LocalizationServiceProvider::class), function (): void {
    it('loads translation files', function (): void {
        $configuration = $this->app->make(Apiato::class)->localization();
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
        $localization = $this->app->make(Apiato::class)->localization();

        $this->app->setLocale('fr');

        expect($localization->paths())->each(function () {
            expect(__('forbidden'))->toBe('interdit');
        });
    });
})->covers(LocalizationServiceProvider::class);

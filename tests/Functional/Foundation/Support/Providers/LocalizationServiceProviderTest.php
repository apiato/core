<?php

declare(strict_types=1);

use Apiato\Foundation\Support\Providers\LocalizationServiceProvider;

describe(class_basename(LocalizationServiceProvider::class), function (): void {
    it('loads translation files', function (): void {
        $localization = apiato()->localization();
        $this->app->setLocale('en');
        foreach ($localization->paths() as $path) {
            expect(__($localization->buildNamespaceFor($path) . '::errors.forbidden'))->toBe('forbidden');
        }

        $this->app->setLocale('fa');
        foreach ($localization->paths() as $path) {
            expect(__($localization->buildNamespaceFor($path) . '::errors.forbidden'))->toBe('ممنوع');
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

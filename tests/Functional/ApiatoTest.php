<?php

use Apiato\Foundation\Apiato;
use Apiato\Foundation\Configuration\Localization;
use Apiato\Foundation\Support\Providers\LocalizationServiceProvider;

describe(class_basename(Apiato::class), function (): void {
    it('can be configured via a closure to customize translation namespaces', function (): void {
        Apiato::configure()
            ->withTranslations(function (Localization $localization): void {
                $localization->buildNamespaceUsing(static fn (string $path): string => 'test');
            })->create();

        app()->register(LocalizationServiceProvider::class, true);

        $this->app->setLocale('fa');
        expect(__('test::errors.forbidden'))->toBe('ممنوع');
    });
});

<?php

use Apiato\Foundation\Apiato;
use Apiato\Foundation\Configuration\Localization;
use Apiato\Foundation\Support\Providers\LocalizationServiceProvider;

describe(class_basename(Apiato::class), function (): void {
    it('can be instantiated without a path', function (): void {
        $basePath = Safe\realpath(__DIR__ . '/../../workbench');

        $apiato = Apiato::configure()->create();

        expect($apiato->basePath())->toBe($basePath);
    });

    it('can infer base path', function (): void {
        $basePath = Safe\realpath(__DIR__ . '/../../');

        expect(Apiato::inferBasePath())->toBe($basePath);
    });

    it('can be configured via a closure to customize translation namespaces', function (): void {
        Apiato::configure()
            ->withTranslations(function (Localization $localization): void {
                $localization->buildNamespaceUsing(static fn (string $path): string => 'test');
            })->create();

        app()->register(LocalizationServiceProvider::class, true);

        $this->app->setLocale('fa');
        expect(__('test::errors.forbidden'))->toBe('ممنوع');
    });
})->covers(Apiato::class);

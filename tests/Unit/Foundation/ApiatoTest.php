<?php

use Apiato\Foundation\Apiato;
use Apiato\Foundation\Configuration\Localization;
use Apiato\Foundation\Middlewares\ProcessETag;
use Apiato\Foundation\Middlewares\Profiler;
use Apiato\Foundation\Middlewares\ValidateJsonContent;
use Apiato\Foundation\Support\Providers\LocalizationServiceProvider;

describe(class_basename(Apiato::class), function (): void {
    it('can be configured via a closure to customize translation namespaces', function (): void {
        Apiato::configure()
            ->withTranslations(function (Localization $localization): void {
                $localization->buildNamespaceUsing(fn (string $path): string => 'test');
            })
            ->create();

        app()->register(LocalizationServiceProvider::class, true);

        $this->app->setLocale('fa');
        expect(__('test::errors.forbidden'))->toBe('ممنوع');
    });

    it('can list Core middlewares', function (): void {
        $middlewares = [
            ValidateJsonContent::class,
            ProcessETag::class,
            Profiler::class,
        ];

        expect(Apiato::instance()->apiMiddlewares())
            ->toBe($middlewares);
    });
})->covers(Apiato::class);

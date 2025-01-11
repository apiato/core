<?php

use Apiato\Foundation\Apiato;
use Apiato\Foundation\Configuration\Localization;
use Apiato\Foundation\Middleware\ProcessETag;
use Apiato\Foundation\Middleware\Profiler;
use Apiato\Foundation\Middleware\ValidateJsonContent;
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

    it('can list Core middlewares', function (): void {
        $middlewares = [
            ValidateJsonContent::class,
            ProcessETag::class,
            Profiler::class,
        ];

        expect($this->app->make(Apiato::class)->apiMiddlewares())
            ->toBe($middlewares);
    });
})->covers(Apiato::class);

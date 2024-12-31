<?php

use Apiato\Core\Generator\GeneratorsServiceProvider;
use Apiato\Core\Providers\ApiatoServiceProvider;
use Apiato\Core\Providers\MacroProviders\CollectionMacroServiceProvider;
use Apiato\Core\Providers\MacroProviders\ConfigMacroServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Http\Kernel;
use Pest\Expectation;
use Tests\Infrastructure\Dummies\AnotherSingletonClass;
use Tests\Infrastructure\Dummies\AnotherSingletonInterface;
use Tests\Infrastructure\Dummies\AnotherUselessClass;
use Tests\Infrastructure\Dummies\AnotherUselessInterface;
use Tests\Infrastructure\Dummies\SingletonClass;
use Tests\Infrastructure\Dummies\SingletonInterface;
use Tests\Infrastructure\Dummies\UselessClass;
use Tests\Infrastructure\Dummies\UselessInterface;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Middlewares\BeforeMiddleware;
use Tests\Infrastructure\Fakes\Providers\AggregateServiceProvider;
use Tests\Infrastructure\Fakes\Providers\DeferredServiceProvider;
use Tests\Infrastructure\Fakes\Providers\FirstServiceProvider;
use Tests\Infrastructure\Fakes\Providers\SecondServiceProvider;

describe(class_basename(ApiatoServiceProvider::class), function (): void {
    it('registers the providers that are listed in the $providers property', function (): void {
        $providers = [
            GeneratorsServiceProvider::class,
            CollectionMacroServiceProvider::class,
            ConfigMacroServiceProvider::class,
            // test providers
            AggregateServiceProvider::class,
            FirstServiceProvider::class,
            SecondServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            expect($this->app->providerIsLoaded($provider))->toBeTrue();
        }
    });

    it('adds aliases from all registered providers', function (): void {
        $aliases = [
            'Foo' => FirstServiceProvider::class,
            'Bar' => SecondServiceProvider::class,
            'Baz' => DeferredServiceProvider::class,
        ];

        $availableAliases = AliasLoader::getInstance()->getAliases();

        foreach ($aliases as $alias => $class) {
            expect($availableAliases[$alias])->toBe($class);
        }
    });

    it('binds the bindings that are listed in the $bindings property', function (): void {
        $bindings = [
            UselessInterface::class => UselessClass::class,
            AnotherUselessInterface::class => AnotherUselessClass::class,
        ];

        foreach ($bindings as $key => $value) {
            expect($this->app->bound($key))->toBeTrue();
            $instance = $this->app->make($key);
            expect($this->app->make($key))->not()->toBe($instance);
        }
    });

    it('binds the singletons that are listed in the $singletons property', function (): void {
        $singletons = [
            SingletonInterface::class => SingletonClass::class,
            AnotherSingletonInterface::class => AnotherSingletonClass::class,
        ];

        foreach ($singletons as $key => $value) {
            expect($this->app->bound($key))->toBeTrue();
            $instance = $this->app->make($key);
            expect($this->app->make($key))->toBe($instance);
        }
    });

    it('respects deferred providers registration', function (): void {
        // we have to test that this provider is not loaded, but it is actually deferred
        // also check that all providers, bindings, singletons and aliases are also working properly
        // and also test that differed provider works on first level provider calls and also any nested provider calls and registration
        expect($this->app->providerIsLoaded(DeferredServiceProvider::class))->toBeFalse();
        expect($this->app->isDeferredService(DeferredServiceProvider::class))->toBeTrue();
    })->todo();

    it('can register middlewares in the service provider', function (): void {
        expect(app(Kernel::class)
            ->hasMiddleware(BeforeMiddleware::class))
            ->toBeTrue();
    });

    describe('can register middlewares via middleware service provider', function (): void {
        it('can register middlewares', function (): void {
            $middlewares = [
                'test-middleware',
            ];

            expect($middlewares)
                ->each(function (Expectation $middleware) {
                    expect($this->app->hasMiddleware($middleware->value))->toBeTrue();
                });
        })->todo();

        it('can register middlewares in groups', function (): void {
            $middlewares = [
                'test-middleware',
            ];

            expect($middlewares)
                ->each(function (Expectation $middleware) {
                    expect($this->app->hasMiddleware($middleware->value))->toBeTrue();
                });
        })->todo();

        it('can register route middlewares', function (): void {
            $middlewares = [
                'test-middleware',
            ];

            expect($middlewares)
                ->each(function (Expectation $middleware) {
                    expect($this->app->hasMiddleware($middleware->value))->toBeTrue();
                });
        })->todo();

        it('can add middlewares aliases', function (): void {
            $middlewares = [
                'test-middleware',
            ];

            expect($middlewares)
                ->each(function (Expectation $middleware) {
                    expect($this->app->hasMiddleware($middleware->value))->toBeTrue();
                });
        })->todo();

        it('can set middleware priority', function (): void {
            $middlewares = [
                'test-middleware',
            ];

            expect($middlewares)
                ->each(function (Expectation $middleware) {
                    expect($this->app->hasMiddleware($middleware->value))->toBeTrue();
                });
        })->todo();
    });
})->coversClass(ApiatoServiceProvider::class);

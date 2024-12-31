<?php

use Apiato\Foundation\Providers\ApiatoServiceProvider;
use Apiato\Foundation\Providers\MacroServiceProvider;
use Apiato\Generator\GeneratorsServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Http\Kernel;
use Pest\Expectation;
use Tests\Support\Doubles\Dummies\AnotherSingletonClass;
use Tests\Support\Doubles\Dummies\AnotherSingletonInterface;
use Tests\Support\Doubles\Dummies\AnotherUselessClass;
use Tests\Support\Doubles\Dummies\AnotherUselessInterface;
use Tests\Support\Doubles\Dummies\SingletonClass;
use Tests\Support\Doubles\Dummies\SingletonInterface;
use Tests\Support\Doubles\Dummies\UselessClass;
use Tests\Support\Doubles\Dummies\UselessInterface;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Middlewares\BeforeMiddleware;
use Tests\Support\Doubles\Fakes\Providers\AggregateServiceProvider;
use Tests\Support\Doubles\Fakes\Providers\DeferredServiceProvider;
use Tests\Support\Doubles\Fakes\Providers\FirstServiceProvider;
use Tests\Support\Doubles\Fakes\Providers\SecondServiceProvider;

describe(class_basename(ApiatoServiceProvider::class), function (): void {
    it('registers the providers that are listed in the $providers property', function (): void {
        $providers = [
            GeneratorsServiceProvider::class,
            MacroServiceProvider::class,
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

    it('registers Core commands', function (): void {
        $actual = collect(Artisan::all());
        $commands = [
            'apiato:list:actions',
            'apiato:list:tasks',
            'apiato:seed-deploy',
            'apiato:seed-test',
        ];

        expect($commands)
            ->each(function (Expectation $command) use ($actual) {
                expect($actual->has($command->value))->toBeTrue();
            });
    });




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
})->covers(ApiatoServiceProvider::class);

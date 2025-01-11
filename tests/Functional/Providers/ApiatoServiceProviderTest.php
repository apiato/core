<?php

use Apiato\Foundation\Database\DatabaseSeeder;
use Apiato\Foundation\Providers\ApiatoServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Support\Facades\DB;
use Tests\Support\Doubles\Dummies\AnotherSingletonClass;
use Tests\Support\Doubles\Dummies\AnotherSingletonInterface;
use Tests\Support\Doubles\Dummies\AnotherUselessClass;
use Tests\Support\Doubles\Dummies\AnotherUselessInterface;
use Tests\Support\Doubles\Dummies\SingletonClass;
use Tests\Support\Doubles\Dummies\SingletonInterface;
use Tests\Support\Doubles\Dummies\UselessClass;
use Tests\Support\Doubles\Dummies\UselessInterface;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\Identity\User\Providers\FirstServiceProvider;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Author\Providers\DeferredServiceProvider;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Middlewares\BeforeMiddleware;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Providers\SecondServiceProvider;

describe(class_basename(ApiatoServiceProvider::class), function (): void {
    it('registers the providers that are listed in the $providers property', function (): void {
        /** @var ApiatoServiceProvider $provider */
        $provider = $this->app->getProvider(ApiatoServiceProvider::class);

        foreach ($provider->providers() as $provider) {
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
            expect($availableAliases)->toHaveKey($alias, $class);
        }
    });

    it('can add its own aliases', function (): void {
        $aliases = [
            'DatabaseSeeder' => DatabaseSeeder::class,
        ];

        $availableAliases = AliasLoader::getInstance()->getAliases();

        foreach ($aliases as $alias => $class) {
            expect($availableAliases)->toHaveKey($alias, $class);
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

    it('can register middlewares in service provider', function (): void {
        expect(app(Kernel::class)
            ->hasMiddleware(BeforeMiddleware::class))
            ->toBeTrue();
    });

    it('overrides default Laravel seeder with Apiato seeder', function (): void {
        $this->artisan('db:seed')
            ->assertExitCode(0);

        expect(DB::table('books')->count())->toBe(8);

        $this->artisan('migrate --seed')
            ->assertExitCode(0);

        expect(DB::table('books')->count())->toBe(16);
    });
})->covers(ApiatoServiceProvider::class);

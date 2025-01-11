<?php

use Apiato\Foundation\Database\DatabaseSeeder;
use Apiato\Foundation\Providers\ApiatoServiceProvider;
use Apiato\Foundation\Providers\MacroServiceProvider;
use Apiato\Foundation\Support\Providers\CommandServiceProvider;
use Apiato\Foundation\Support\Providers\ConfigServiceProvider;
use Apiato\Foundation\Support\Providers\HelperServiceProvider;
use Apiato\Foundation\Support\Providers\LocalizationServiceProvider;
use Apiato\Foundation\Support\Providers\MigrationServiceProvider;
use Apiato\Foundation\Support\Providers\RateLimitingServiceProvider;
use Apiato\Foundation\Support\Providers\ViewServiceProvider;
use Apiato\Generator\GeneratorsServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Support\Facades\DB;
use Tests\Support\Doubles\Dummies\DeferredSingletonInterface;
use Tests\Support\Doubles\Dummies\DeferredUselessInterface;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Middlewares\BeforeMiddleware;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Providers\DeferrableServiceProvider;

describe(class_basename(ApiatoServiceProvider::class), function (): void {
    it('registers expected providers', function (): void {
        $providers = [
            GeneratorsServiceProvider::class,
            MacroServiceProvider::class,
            CommandServiceProvider::class,
            ConfigServiceProvider::class,
            HelperServiceProvider::class,
            LocalizationServiceProvider::class,
            MigrationServiceProvider::class,
            RateLimitingServiceProvider::class,
            ViewServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            expect($this->app->providerIsLoaded($provider))->toBeTrue();
        }
    });

    it('adds database alias', function (): void {
        $availableAliases = AliasLoader::getInstance()->getAliases();

        expect($availableAliases)->toHaveKey('DatabaseSeeder', DatabaseSeeder::class);
    });

    beforeEach(function (): void {
        $this->artisan('optimize:clear');
    });
    it('respects deferred providers registration', function (): void {
        // we have to test that this provider is not loaded, but it is actually deferred
        // also check that all providers, bindings, singletons and aliases are also working properly
        // and also test that differed provider works on first level provider calls and also any nested provider calls and registration
        //        expect(new DeferrableServiceProvider($this->app))->isDeferred()->toBeTrue();
        expect($this->app->providerIsLoaded(DeferrableServiceProvider::class))->toBeFalse();
        //        expect($this->app->isDeferredService(DeferredUselessInterface::class))->toBeTrue();
        //        expect($this->app->isDeferredService(DeferredSingletonInterface::class))->toBeTrue();
        //        expect($this->app->isDeferredService('TestDeferredProviderAlias'))->toBeTrue();
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

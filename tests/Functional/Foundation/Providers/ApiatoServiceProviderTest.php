<?php

use Apiato\Foundation\Database\DatabaseSeeder;
use Apiato\Foundation\Providers\ApiatoServiceProvider;
use Apiato\Foundation\Support\Providers\ConfigServiceProvider;
use Apiato\Foundation\Support\Providers\HelperServiceProvider;
use Apiato\Foundation\Support\Providers\LocalizationServiceProvider;
use Apiato\Foundation\Support\Providers\MigrationServiceProvider;
use Apiato\Foundation\Support\Providers\RateLimitingServiceProvider;
use Apiato\Foundation\Support\Providers\ViewServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\HashidsManager;
use Workbench\App\Containers\MySection\Book\Middlewares\BeforeMiddleware;

describe(class_basename(ApiatoServiceProvider::class), function (): void {
    it('registers expected providers', function (): void {
        $providers = [
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

    it('extends hashids service provider', function (): void {
        expect(hashids()->tryDecode('abc'))->toBeNull();
    });
})->covers(ApiatoServiceProvider::class);

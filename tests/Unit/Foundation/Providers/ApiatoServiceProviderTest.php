<?php

namespace Tests\Unit\Foundation\Support\Providers;

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
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\Identity\User\Providers\FirstServiceProvider;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Author\Providers\DeferredServiceProvider;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Providers\BookServiceProvider;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Providers\EventServiceProvider;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Providers\ThirdServiceProvider;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Providers\SecondServiceProvider;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Providers\ShipServiceProvider;

describe(class_basename(ApiatoServiceProvider::class), function (): void {
    it('registers expected providers', function (): void {
        /** @var ApiatoServiceProvider $provider */
        $provider = $this->app->getProvider(ApiatoServiceProvider::class);
        $expected = [
            GeneratorsServiceProvider::class,
            MacroServiceProvider::class,
            CommandServiceProvider::class,
            ConfigServiceProvider::class,
            HelperServiceProvider::class,
            LocalizationServiceProvider::class,
            MigrationServiceProvider::class,
            RateLimitingServiceProvider::class,
            ViewServiceProvider::class,
            SecondServiceProvider::class,
            ShipServiceProvider::class,
            BookServiceProvider::class,
            EventServiceProvider::class,
            ThirdServiceProvider::class,
            DeferredServiceProvider::class,
            FirstServiceProvider::class,
        ];

        $registeredProviders = $provider->providers();

        expect($registeredProviders)->toBe($expected);
    });

    it('adds expected aliases', function (): void {
        /** @var ApiatoServiceProvider $provider */
        $provider = $this->app->getProvider(ApiatoServiceProvider::class);
        $expected = [
            'DatabaseSeeder' => DatabaseSeeder::class,
        ];

        $aliases = $provider->aliases();

        expect($aliases)->toBe($expected);
    });
})->covers(ApiatoServiceProvider::class);

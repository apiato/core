<?php

use Apiato\Foundation\Loaders\Apiato;
use Apiato\Foundation\Loaders\ApplicationBuilder;
use Apiato\Foundation\Middlewares\ProcessETag;
use Apiato\Foundation\Middlewares\Profiler;
use Apiato\Foundation\Middlewares\ValidateJsonContent;
use Illuminate\Support\Facades\Event;
use Pest\Expectation;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Events\BookCreated;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Listeners\BookCreatedListener;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Providers\BookServiceProvider;
use Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Providers\EventServiceProvider;
use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Providers\ShipServiceProvider;

describe(class_basename(ApplicationBuilder::class), function (): void {
    it('provides default paths', function (): void {
        $builder = Apiato::configure(__DIR__);
        expect($builder->providerPaths())->toBe([
            __DIR__ . '/app/Ship/Providers',
            __DIR__ . '/app/Containers/*/*/Providers',
        ])->and($builder->configPaths())->toBe([
            __DIR__ . '/app/Ship/Configs',
            __DIR__ . '/app/Containers/*/*/Configs',
        ])->and($builder->eventPaths())->toBe([
            __DIR__ . '/app/Ship/Listeners',
            __DIR__ . '/app/Containers/*/*/Listeners',
        ]);
    })->todo();

    it('registers providers from configured path', function (): void {
        $providers = [
            ShipServiceProvider::class,
            BookServiceProvider::class,
            EventServiceProvider::class,
        ];

        foreach ($providers as $provider) {
            expect($this->app->providerIsLoaded($provider))->toBeTrue();
        }
    });

    it('merges configs from configured path', function (): void {
        expect(config('boat'))->toBe([
            'test' => 'boat',
        ])->and(config('mySection-book'))->toBe([
            'test' => 'book',
        ]);
    });

    it('can discover events from configured path', function (): void {
        Event::fake()
            ->assertListening(
                BookCreated::class,
                BookCreatedListener::class,
            );
    });

    it('can manually register events', function (): void {
        Event::fake()
            ->assertListening(
                BookCreated::class,
                BookCreatedListener::class,
            );
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

    it('registers commands from configured path', function (): void {
        $actual = collect(Artisan::all());
        $commands = [
            'ship:test-command',
            'container:test-command',
        ];

        expect($commands)
            ->each(function (Expectation $command) use ($actual) {
                expect($actual->has($command->value))->toBeTrue();
            });
    });

    it('can list Core middlewares', function (): void {
        $middlewares = [
            ValidateJsonContent::class,
            ProcessETag::class,
            Profiler::class,
        ];

        expect((new ApplicationBuilder())->apiMiddlewares())
            ->toBe($middlewares);
    });
});

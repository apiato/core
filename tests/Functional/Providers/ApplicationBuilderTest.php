<?php

use Apiato\Foundation\Apiato;
use Apiato\Foundation\Configuration\ApplicationBuilder;
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

    it('registers commands from configured path', function (): void {
        $mustLoad = [
            'ship:test-command',
            'container:test-command',
            'apiato:seed-deploy',
            'apiato:seed-test',
        ];

        $registeredCommands = collect(Artisan::all());

        expect($mustLoad)
            ->each(function (Expectation $command) use ($registeredCommands) {
                expect($registeredCommands->has($command->value))->toBeTrue();
            });
    });

    it('load web routes from configured path', function (): void {
        $endpoints = [
            '/authors',
            '/books',
        ];

        expect($endpoints)
            ->each(function (Expectation $endpoint) {
                $response = $this->get($endpoint->value);
                $response->assertOk();
            });
    });

    it('load api routes from configured path', function (): void {
        $endpoints = [
            '/v3/authors',
            '/v1/books',
        ];

        expect($endpoints)
            ->each(function (Expectation $endpoint) {
                $response = $this->get($endpoint->value);
                $response->assertOk();
            });
    });
})->covers(ApplicationBuilder::class);

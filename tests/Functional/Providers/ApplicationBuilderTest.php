<?php

use Apiato\Foundation\Apiato;
use Apiato\Foundation\Configuration\ApplicationBuilder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Pest\Expectation;
use Workbench\App\Containers\MySection\Author\Events\AuthorCreated;
use Workbench\App\Containers\MySection\Author\Listeners\AuthorCreatedListener;
use Workbench\App\Containers\MySection\Book\Events\BookCreated;
use Workbench\App\Containers\MySection\Book\Listeners\BookCreatedListener;
use Workbench\App\Containers\MySection\Book\Providers\BookServiceProvider;
use Workbench\App\Containers\MySection\Book\Providers\EventServiceProvider;
use Workbench\App\Ship\Providers\ShipServiceProvider;

describe(class_basename(ApplicationBuilder::class), function (): void {
    it('provides default paths', function (): void {
        $config = Apiato::configure(__DIR__)->create();
        expect($config->providers())->toBe([
            shared_path('Providers'),
            app_path('Containers/*/*/Providers'),
        ])->and($config->configs())->toBe([
            shared_path('Configs'),
            app_path('Containers/*/*/Configs'),
        ])->and($config->events())->toBe([
            shared_path('Listeners'),
            app_path('Containers/*/*/Listeners'),
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

    it('can auto discover events', function (): void {
        Event::fake()
            ->assertListening(
                AuthorCreated::class,
                AuthorCreatedListener::class,
            );
    });

    it('registers manually registered events', function (): void {
        Event::fake()
            ->assertListening(
                BookCreated::class,
                BookCreatedListener::class,
            );
    });

    it('registers commands from configured path', function (): void {
        $mustLoad = [
            'ship:test-command',
            'container:test-command',
        ];

        $registeredCommands = collect(Artisan::all());

        expect($mustLoad)
            ->each(function (Expectation $command) use ($registeredCommands): void {
                expect($registeredCommands->has($command->value))->toBeTrue();
            });
    });

    it('load web routes from configured path', function (): void {
        $endpoints = [
            '/authors',
            '/books',
        ];

        expect($endpoints)
            ->each(function (Expectation $endpoint): void {
                $response = $this->get($endpoint->value);
                $response->assertOk();
            });
    });

    it('load api routes from configured path', function (): void {
        $endpoints = [
            '/v3/authors',
            '/v4/books',
        ];

        expect($endpoints)
            ->each(function (Expectation $endpoint): void {
                $response = $this->get($endpoint->value);
                $response->assertOk();
            });
    });
})->covers(ApplicationBuilder::class);

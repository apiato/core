<?php

use Apiato\Foundation\Configuration\Provider;
use Apiato\Support\DefaultProviders;
use Workbench\App\RandomServiceProvider;

describe(class_basename(Provider::class), function (): void {
    it('can be instantiated with default providers', function (): void {
        $provider = new Provider();

        expect($provider->toArray())->toBe(DefaultProviders::providers());
    });

    it('can be instantiated with custom providers', function (): void {
        $provider = new Provider([RandomServiceProvider::class]);

        expect($provider->toArray())->toBe([RandomServiceProvider::class]);
    });

    it('can load providers from a given path', function (): void {
        $provider = new Provider();
        $provider->loadFrom(app_path());

        expect($provider->toArray())->toContain(RandomServiceProvider::class);
    });

    it('does not load the same provider twice', function (): void {
        $provider = new Provider();
        $provider->loadFrom(app_path(), app_path());

        expect(
            collect($provider->toArray())
                ->filter(static fn (string $provider): bool => RandomServiceProvider::class === $provider)
                ->count(),
        )->toBe(1);
    });
})->covers(Provider::class);

<?php

use Apiato\Foundation\Support\Providers\ConfigServiceProvider;
use Illuminate\Support\Facades\File;

describe(class_basename(ConfigServiceProvider::class), function (): void {
    it('merges configs from configured path', function (): void {
        expect(config('boat'))->toBe([
            'test' => 'boat',
        ])->and(config('mySection-book'))->toBe([
            'test' => 'book',
        ]);
    });

    it('publishes the config file', function (): void {
        File::partialMock()
            ->expects('copy')
            ->withArgs(
                static fn (string $path, string $target): bool => Str::of($path)->contains('config/apiato.php')
                    && (shared_path('Configs/apiato.php') === $target),
            )->andReturnTrue();

        $this->artisan('vendor:publish', ['--tag' => 'apiato-config']);
    });
})->covers(ConfigServiceProvider::class);

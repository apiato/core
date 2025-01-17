<?php

use Apiato\Foundation\Support\Providers\ConfigServiceProvider;

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
            ->with(realpath('config/apiato.php'), shared_path('Configs/apiato.php'))
            ->andReturnTrue();

        $this->artisan('vendor:publish', ['--tag' => 'apiato-config']);
    });
})->covers(ConfigServiceProvider::class);

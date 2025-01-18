<?php

use Apiato\Foundation\Providers\CommandServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Pest\Expectation;

describe(class_basename(CommandServiceProvider::class), function (): void {
    it('registers Core commands', function (): void {
        $actual = collect(Artisan::all());
        $commands = [
            'apiato:list:actions',
            'apiato:list:tasks',
        ];

        expect($commands)
            ->each(function (Expectation $command) use ($actual): void {
                expect($actual->has($command->value))->toBeTrue();
            });
    });
})->covers(CommandServiceProvider::class);

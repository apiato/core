<?php

use Apiato\Console\ListActions;

describe(class_basename(ListActions::class), function (): void {
    it('can list all actions', function (): void {
        $this->artisan('apiato:list:actions')
            ->assertExitCode(0)
            ->expectsOutput('[Author]')
            ->expectsOutput(' - Simple')
            ->expectsOutput('[Book]')
            ->expectsOutput(' - Create Book');
    });

    it('can list all actions with file name', function (): void {
        $this->artisan('apiato:list:actions', [
            '--with-file-name' => true,
        ])->assertExitCode(0)
            ->expectsOutput('[Author]')
            ->expectsOutput(' - Simple (SimpleAction.php)')
            ->expectsOutput('[Book]')
            ->expectsOutput(' - Create Book (CreateBookAction.php)');
    });
})->covers(ListActions::class);

<?php

use Apiato\Console\Commands\ListTasks;

describe(class_basename(ListTasks::class), function (): void {
    it('can list all actions', function (): void {
        $this->artisan('apiato:list:tasks')
            ->assertExitCode(0)
            ->expectsOutput('[Book]')
            ->expectsOutput(' - Create Book');
    });

    it('can list all actions with file name', function (): void {
        $this->artisan('apiato:list:tasks', [
            '--with-file-name' => true,
        ])->assertExitCode(0)
            ->expectsOutput('[Book]')
            ->expectsOutput(' - Create Book (CreateBookTask.php)');
    });
})->covers(ListTasks::class);

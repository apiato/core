<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Ship\Commands;

use Apiato\Core\Abstracts\Commands\Command;

class ShipTestCommand extends Command
{
    protected $signature = 'ship:test-command';

    public function handle(): void
    {
        $this->info('Ship command works!');
    }
}
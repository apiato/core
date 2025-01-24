<?php

namespace Workbench\App\Ship\Commands;

use Apiato\Abstract\Commands\Command;

final class ShipTestCommand extends Command
{
    protected $signature = 'ship:test-command';

    public function handle(): void
    {
        $this->info('Ship command works!');
    }
}

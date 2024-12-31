<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\UI\Console\Commands;

use Apiato\Abstract\Commands\Command;

class ContainerTestCommand extends Command
{
    protected $signature = 'container:test-command';

    public function handle(): void
    {
        $this->info('Container command works!');
    }
}

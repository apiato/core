<?php

namespace Workbench\App\Containers\MySection\Book\UI\CLI;

use Apiato\Abstract\Commands\Command;

class ContainerTestCommand extends Command
{
    protected $signature = 'container:test-command';

    public function handle(): void
    {
        $this->info('Container command works!');
    }
}

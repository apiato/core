<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\UI\Console\Commands;

use Apiato\Core\Abstracts\Commands\Command;

class ContainerTestCommand extends Command
{
    protected $signature = 'container:test-command';

    public function handle(): void
    {
        $this->info('Container command works!');
    }
}
<?php

namespace Apiato\Console;

use Apiato\Console\Commands\ListActions;
use Apiato\Console\Commands\ListTasks;
use Apiato\Core\Providers\ServiceProvider;

final class CommandServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            ListActions::class,
            ListTasks::class,
        ]);
    }
}

<?php

namespace Apiato\Console;

use Apiato\Abstract\Providers\ServiceProvider;
use Apiato\Console\Commands\ListActions;
use Apiato\Console\Commands\ListTasks;

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

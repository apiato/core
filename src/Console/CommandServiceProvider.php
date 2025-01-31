<?php

namespace Apiato\Console;

use Apiato\Abstract\Providers\ServiceProvider;
use Apiato\Console\ListActions;
use Apiato\Console\ListTasks;

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

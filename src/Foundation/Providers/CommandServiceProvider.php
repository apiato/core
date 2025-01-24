<?php

namespace Apiato\Foundation\Providers;

use Apiato\Abstract\Providers\ServiceProvider;
use Apiato\Commands\ListActions;
use Apiato\Commands\ListTasks;

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

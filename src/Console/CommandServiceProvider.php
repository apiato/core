<?php

namespace Apiato\Console;

use Apiato\Abstract\Providers\ServiceProvider;

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

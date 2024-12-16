<?php

namespace Apiato\Core\Abstracts\Providers;

use Apiato\Core\Foundation\Facades\Apiato;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as LaravelEventServiceProvider;

abstract class EventServiceProvider extends LaravelEventServiceProvider
{
    public function shouldDiscoverEvents(): bool
    {
        return true;
    }

    protected function discoverEventsWithin(): array
    {
        return array_map(static fn (string $path) => $path . '/Listeners', Apiato::getAllContainerPaths());
    }
}

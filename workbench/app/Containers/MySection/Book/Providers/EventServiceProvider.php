<?php

namespace Workbench\App\Containers\MySection\Book\Providers;

use Workbench\App\Containers\MySection\Book\Events\BookCreated;
use Workbench\App\Containers\MySection\Book\Listeners\BookCreatedListener;
use Workbench\App\Ship\Parents\Providers\EventServiceProvider as ParentEventServiceProvider;

class EventServiceProvider extends ParentEventServiceProvider
{
    protected $listen = [
        BookCreated::class => [
            BookCreatedListener::class,
        ],
    ];
}

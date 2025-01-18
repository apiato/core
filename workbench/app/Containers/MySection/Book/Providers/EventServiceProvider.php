<?php

namespace Workbench\App\Containers\MySection\Book\Providers;

use Workbench\App\Ship\Parents\Providers\EventServiceProvider as ParentEventServiceProvider;

class EventServiceProvider extends ParentEventServiceProvider
{
    protected $listen = [
        //        BookCreatedListener::class => [
        //            BookCreated::class,
        //        ],
    ];
}

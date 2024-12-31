<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Providers;

use Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Providers\EventServiceProvider as ParentEventServiceProvider;

class EventServiceProvider extends ParentEventServiceProvider
{
    protected $listen = [
        //        BookCreatedListener::class => [
        //            BookCreated::class,
        //        ],
    ];
}

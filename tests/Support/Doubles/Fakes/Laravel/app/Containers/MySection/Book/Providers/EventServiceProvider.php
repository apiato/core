<?php

namespace Tests\Support\Doubles\Fakes\Laravel\app\Containers\MySection\Book\Providers;

use Tests\Support\Doubles\Fakes\Laravel\app\Ship\Parents\Providers\EventServiceProvider as ParentEventServiceProvider;

class EventServiceProvider extends ParentEventServiceProvider
{
    protected $listen = [
        //        BookCreatedListener::class => [
        //            BookCreated::class,
        //        ],
    ];
}

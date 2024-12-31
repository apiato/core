<?php

namespace Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Tests\Infrastructure\Fakes\Laravel\app\Containers\MySection\Book\Events\BookCreated;
use Tests\Infrastructure\Fakes\Laravel\app\Ship\Parents\Listeners\Listener as ParentListener;

class BookCreatedListener extends ParentListener implements ShouldQueue
{
    public function __construct()
    {
    }

    public function __invoke(BookCreated $event): void
    {
    }
}

<?php

namespace Workbench\App\Containers\MySection\Book\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Workbench\App\Containers\MySection\Book\Events\BookCreated;
use Workbench\App\Ship\Parents\Listeners\Listener as ParentListener;

class BookCreatedListener extends ParentListener implements ShouldQueue
{
    public function __construct()
    {
    }

    public function __invoke(BookCreated $event): void
    {
    }
}

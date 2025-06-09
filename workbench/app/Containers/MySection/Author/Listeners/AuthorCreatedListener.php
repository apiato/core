<?php

declare(strict_types=1);

namespace Workbench\App\Containers\MySection\Author\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Workbench\App\Containers\MySection\Author\Events\AuthorCreated;
use Workbench\App\Ship\Parents\Listeners\Listener as ParentListener;

class AuthorCreatedListener extends ParentListener implements ShouldQueue
{
    public function __invoke(AuthorCreated $event): void
    {
        // TODO: For the sake of this example, we are not doing anything here.
    }
}

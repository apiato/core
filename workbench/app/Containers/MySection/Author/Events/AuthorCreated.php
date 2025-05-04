<?php

namespace Workbench\App\Containers\MySection\Author\Events;

use Workbench\App\Ship\Parents\Events\Event as ParentEvent;

class AuthorCreated extends ParentEvent
{
    public function __construct()
    {
    }
}

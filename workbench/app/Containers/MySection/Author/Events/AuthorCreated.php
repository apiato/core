<?php

declare(strict_types=1);

namespace Workbench\App\Containers\MySection\Author\Events;

use Workbench\App\Containers\Identity\User\Models\User;
use Workbench\App\Ship\Parents\Events\Event as ParentEvent;

class AuthorCreated extends ParentEvent
{
    public function __construct(public readonly User $author)
    {
    }
}

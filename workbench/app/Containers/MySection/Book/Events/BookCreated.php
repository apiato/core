<?php

namespace Workbench\App\Containers\MySection\Book\Events;

use Workbench\App\Containers\MySection\Book\Models\Book;
use Workbench\App\Ship\Parents\Events\Event as ParentEvent;

class BookCreated extends ParentEvent
{
    public function __construct(
        public readonly Book $book,
    ) {
    }
}

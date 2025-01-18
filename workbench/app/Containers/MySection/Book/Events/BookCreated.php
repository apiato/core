<?php

namespace Workbench\App\Containers\MySection\Book\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Workbench\App\Containers\MySection\Book\Models\Book;
use Workbench\App\Ship\Parents\Events\Event as ParentEvent;

class BookCreated extends ParentEvent
{
    public function __construct(
        public readonly Book $book,
    ) {
    }

    /**
     * @return Channel[]
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}

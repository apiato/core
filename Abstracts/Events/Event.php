<?php

namespace Apiato\Core\Abstracts\Events;

use Apiato\Core\Abstracts\Events\Traits\JobProperties;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class Event
{
    use JobProperties;
    use Dispatchable, InteractsWithSockets, SerializesModels;
}
